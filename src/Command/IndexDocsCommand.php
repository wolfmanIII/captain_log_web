<?php

namespace App\Command;

use App\Entity\DocumentChunk;
use App\Service\DocumentTextExtractor;
use Doctrine\ORM\EntityManagerInterface;
use OpenAI;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:index-docs')]
class IndexDocsCommand extends Command
{
    // cartelle da escludere (relative a var/knowledge)
    private array $excludedDirs = [
        'images',
        'img',
        'tmp',
        '.git',
        '.idea',
    ];

    // pattern per filename da escludere (regex)
    private array $excludedNamePatterns = [
        '/^~.*$/',         // file temporanei tipo ~qualcosa.docx
        '/^\.~lock\..*/',  // lock file LibreOffice
        '/^\.gitkeep$/',   // file di servizio
        '/^\.DS_Store$/',  // Mac
    ];

    // estensioni supportate
    private array $extensions = ['pdf', 'md', 'odt', 'docx'];

    public function __construct(
        private EntityManagerInterface $em,
        private DocumentTextExtractor $extractor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Indicizza i documenti in var/knowledge (PDF/MD/ODT/DOCX) con embeddings.')
            ->addOption(
                'force-reindex',
                null,
                InputOption::VALUE_NONE,
                'Ignora hash e reindicizza tutti i file (anche se non modificati)'
            )
            ->addOption(
                'path',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Sotto-percorso relativo da indicizzare (es: "manuali", "log/2025"). Puoi usarlo più volte.',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rootDir = __DIR__ . '/../../var/knowledge';

        if (!is_dir($rootDir)) {
            $output->writeln('<error>Cartella non trovata: '.$rootDir.'</error>');
            return Command::FAILURE;
        }

        $forceReindex = (bool) $input->getOption('force-reindex');
        /** @var string[] $pathsFilter */
        $pathsFilter  = $input->getOption('path') ?? [];

        if (!empty($pathsFilter)) {
            // normalizziamo rimuovendo slash iniziali/finali
            $pathsFilter = array_map(static function (string $p) {
                return trim($p, '/');
            }, $pathsFilter);

            $output->writeln('<info>Filtro path attivo:</info> '.implode(', ', $pathsFilter));
        }

        if ($forceReindex) {
            $output->writeln('<comment>Opzione --force-reindex attiva: tutti i file saranno reindicizzati.</comment>');
        }

        // 1) Raccogliamo tutti i file candidati (ricorsivo)
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootDir, \FilesystemIterator::SKIP_DOTS)
        );

        $files = [];

        foreach ($iterator as $filePath => $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $ext = strtolower($fileInfo->getExtension());
            if (!in_array($ext, $this->extensions, true)) {
                continue; // estensione non supportata
            }

            // path relativo rispetto a rootDir, es: "manuali/cap1.pdf"
            $relPath = substr($filePath, strlen($rootDir) + 1);
            $dirName = trim(dirname($relPath), '.');

            // 1a) esclusione cartelle
            if ($this->isInExcludedDir($dirName)) {
                continue;
            }

            // 1b) esclusione filename per pattern
            $fileName = $fileInfo->getFilename();
            if ($this->isExcludedName($fileName)) {
                continue;
            }

            // 1c) filtro per sotto-percorsi (se specificato)
            if (!empty($pathsFilter) && !$this->matchesPathsFilter($relPath, $pathsFilter)) {
                continue;
            }

            $files[] = $filePath;
        }

        if (!$files) {
            $output->writeln('<comment>Nessun file supportato/filtro corrispondente trovato in '.$rootDir.'</comment>');
            return Command::SUCCESS;
        }

        // 2) Progress bar se verbose
        $progressBar = null;
        if ($output->isVerbose()) {
            $progressBar = new ProgressBar($output, count($files));
            $progressBar->start();
        }

        // 3) Client OpenAI
        $client = OpenAI::client($_ENV['OPENAI_API_KEY']);

        foreach ($files as $file) {
            $relPath   = substr($file, strlen($rootDir) + 1); // es: "manuali/cap1.pdf"
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $fileHash  = hash_file('sha256', $file);

            if ($output->isVeryVerbose()) {
                $output->writeln("\nFile: <info>$relPath</info>");
            }

            // 3a) se NON forceReindex, controlla se esiste già path+hash
            if (!$forceReindex) {
                $existing = $this->em->createQueryBuilder()
                    ->select('COUNT(c.id)')
                    ->from(DocumentChunk::class, 'c')
                    ->where('c.path = :path')
                    ->andWhere('c.fileHash = :hash')
                    ->setParameter('path', $relPath)
                    ->setParameter('hash', $fileHash)
                    ->getQuery()
                    ->getSingleScalarResult();

                if ((int)$existing > 0) {
                    if ($output->isVeryVerbose()) {
                        $output->writeln('  -> Nessuna modifica (hash uguale), salto');
                    }
                    if ($progressBar) {
                        $progressBar->advance();
                    }
                    continue;
                }
            }

            // 3b) estrai testo
            $text = $this->extractor->extract($file);
            if ($text === null || $text === '') {
                if ($output->isVeryVerbose()) {
                    $output->writeln('  -> Nessun testo estratto, salto');
                }
                if ($progressBar) {
                    $progressBar->advance();
                }
                continue;
            }

            // 3c) elimina vecchi chunk (per path) prima di reinserire
            $this->em->createQueryBuilder()
                ->delete(DocumentChunk::class, 'c')
                ->where('c.path = :path')
                ->setParameter('path', $relPath)
                ->getQuery()
                ->execute();

            // 3d) spezza testo in chunk e salva con embedding
            $chunks = $this->splitIntoChunks($text, 1000);
            $now    = new \DateTimeImmutable();

            foreach ($chunks as $index => $chunkText) {
                // embedding del chunk
                $embResp = $client->embeddings()->create([
                    'model' => 'text-embedding-3-small',
                    'input' => $chunkText,
                ]);
                $embedding = $embResp->embeddings[0]->embedding;

                $chunk = (new DocumentChunk())
                    ->setPath($relPath)
                    ->setExtension($extension)
                    ->setChunkIndex($index)
                    ->setContent($chunkText)
                    ->setIndexedAt($now)
                    ->setFileHash($fileHash)
                    ->setEmbedding($embedding);

                $this->em->persist($chunk);
            }

            $this->em->flush();
            $this->em->clear();

            if ($output->isVeryVerbose()) {
                $output->writeln('  -> Indicizzato (' . count($chunks) . ' chunk)');
            }

            if ($progressBar) {
                $progressBar->advance();
            }
        }

        if ($progressBar) {
            $progressBar->finish();
            $output->writeln(''); // newline
        }

        $output->writeln('<info>Indicizzazione completata.</info>');

        return Command::SUCCESS;
    }

    private function splitIntoChunks(string $text, int $maxLen): array
    {
        $text = preg_replace('/\s+/', ' ', $text);
        $chunks = [];

        while (mb_strlen($text) > $maxLen) {
            $slice = mb_substr($text, 0, $maxLen);
            $pos   = mb_strrpos($slice, '.');

            if ($pos === false) {
                $pos = $maxLen;
            }

            $chunks[] = trim(mb_substr($text, 0, $pos));
            $text     = trim(mb_substr($text, $pos));
        }

        if ($text !== '') {
            $chunks[] = $text;
        }

        return $chunks;
    }

    private function isInExcludedDir(string $dirName): bool
    {
        if ($dirName === '.' || $dirName === '') {
            return false;
        }

        // splitta percorso in segmenti e verifica se uno è in excludedDirs
        $segments = explode(DIRECTORY_SEPARATOR, $dirName);
        foreach ($segments as $seg) {
            if (in_array($seg, $this->excludedDirs, true)) {
                return true;
            }
        }

        return false;
    }

    private function isExcludedName(string $fileName): bool
    {
        foreach ($this->excludedNamePatterns as $pattern) {
            if (preg_match($pattern, $fileName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ritorna true se il path relativo rientra in uno dei sotto-percorsi richiesti.
     *
     * Esempi:
     *   relPath: "manuali/cap1.pdf"
     *   filters: ["manuali"]           → true
     *   filters: ["log"]               → false
     *   relPath: "log/2025/eventoA.md"
     *   filters: ["log/2025"]          → true
     */
    private function matchesPathsFilter(string $relPath, array $pathsFilter): bool
    {
        $relPathNorm = ltrim($relPath, '/');

        foreach ($pathsFilter as $filter) {
            $filterNorm = trim($filter, '/');

            if ($relPathNorm === $filterNorm) {
                return true;
            }

            if (str_starts_with($relPathNorm, $filterNorm.'/')) {
                return true;
            }
        }

        return false;
    }
}
