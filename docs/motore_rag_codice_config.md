# Motore AI RAG — Documentazione Codice & Configurazioni

Versione: 1.0 
Progetto: **IA RAG Engine(IRE)** (Symfony 7.3)

Questo documento raccoglie gli **estratti di codice** e le **configurazioni chiave** che riguardano esclusivamente il motore RAG:

- entità Doctrine (`DocumentFile`, `DocumentChunk`);
- servizi (`DocumentTextExtractor`, `ChatbotService`);
- comandi CLI (`app:index-docs`, `app:list-docs`, `app:unindex-file`);
- middleware pgvector;
- configurazioni Doctrine;
- chiamate OpenAI (embedding + chat);
- variabili di ambiente di supporto.

---

## 1. Entità Doctrine

### 1.1 `DocumentFile`

Rappresenta un file indicizzato nella knowledge base.

```php
<?php

namespace App\Entity;

use App\Repository\DocumentFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentFileRepository::class)]
class DocumentFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Path relativo rispetto a var/knowledge, es: "manuali/helix.md"
    #[ORM\Column(length: 512, unique: true)]
    private ?string $path = null;

    // Estensione del file (pdf, md, odt, docx, ...)
    #[ORM\Column(length: 16)]
    private ?string $extension = null;

    // Hash SHA-256 del contenuto del file
    #[ORM\Column(length: 64)]
    private ?string $hash = null;

    // Timestamp di indicizzazione
    #[ORM\Column]
    private \DateTimeImmutable $indexedAt;

    /**
     * @var Collection<int, DocumentChunk>
     */
    #[ORM\OneToMany(
        mappedBy: 'file',
        targetEntity: DocumentChunk::class,
        orphanRemoval: true
    )]
    private Collection $chunks;

    public function __construct()
    {
        $this->indexedAt = new \DateTimeImmutable();
        $this->chunks = new ArrayCollection();
    }

    // getter/setter standard...
}
```

### 1.2 `DocumentChunk`

Rappresenta uno spezzone di testo di un `DocumentFile` con embedding vettoriale.

```php
<?php

namespace App\Entity;

use App\Repository\DocumentChunkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentChunkRepository::class)]
class DocumentChunk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Relazione al file di origine
    #[ORM\ManyToOne(inversedBy: 'chunks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?DocumentFile $file = null;

    // Indice progressivo del chunk all'interno del file
    #[ORM\Column]
    private int $chunkIndex = 0;

    // Testo del chunk (normalizzato)
    #[ORM\Column(type: 'text')]
    private string $content = '';

    // Embedding vettoriale (pgvector, dimensione 1536)
    #[ORM\Column(type: 'vector', length: 1536)]
    private array $embedding = [];

    // getter/setter standard...
}
```

---

## 2. Servizi

### 2.1 `DocumentTextExtractor`

Si occupa di estrarre il testo “puro” da file PDF, Markdown, ODT, DOCX.

```php
<?php

namespace App\Service;

use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class DocumentTextExtractor
{
    public function __construct(
        private PdfParser $pdfParser,
    ) {}

    public function extract(string $path): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf'  => $this->extractFromPdf($path),
            'md'   => $this->extractFromMarkdown($path),
            'odt'  => $this->extractFromOdt($path),
            'docx' => $this->extractFromDocx($path),
            default => null,
        };
    }

    private function extractFromPdf(string $path): ?string
    {
        try {
            $pdf  = $this->pdfParser->parseFile($path);
            $text = $pdf->getText();

            $text = $this->normalizeWhitespace($text);
            $text = $this->stripEmoji($text);

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            // logging eventuale...
            return null;
        }
    }

    private function extractFromMarkdown(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $text = file_get_contents($path) ?: '';
        // eventuale pulizia base di sintassi markdown...
        $text = $this->normalizeWhitespace($text);
        $text = $this->stripEmoji($text);

        return $text !== '' ? $text : null;
    }

    private function extractFromOdt(string $path): ?string
    {
        // lettura via ZipArchive + content.xml
        // parsing XML minimale → testo
        // normalizeWhitespace + stripEmoji
        // ...
        return null; // implementazione effettiva nel progetto
    }

    private function extractFromDocx(string $path): ?string
    {
        // utilizzo PhpWord per leggere contenuto DOCX
        // normalizeWhitespace + stripEmoji
        // ...
        return null; // implementazione effettiva nel progetto
    }

    private function normalizeWhitespace(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }

    private function stripEmoji(string $text): string
    {
        // Rimozione di emoji e simboli vari (range Unicode indicativo)
        return preg_replace('/[\x{1F300}-\x{1FAFF}]/u', '', $text);
    }
}
```

---

### 2.2 `ChatbotService`

Servizio che implementa il flusso di query RAG.

```php
<?php

namespace App\Service;

use App\Entity\DocumentChunk;
use Doctrine\ORM\EntityManagerInterface;
use OpenAI;

class ChatbotService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function ask(string $question): string
    {
        $testMode = ($_ENV['APP_AI_TEST_MODE'] ?? 'false') === 'true';
        $fallback = ($_ENV['APP_AI_OFFLINE_FALLBACK'] ?? 'true') === 'true';
        $model    = $_ENV['APP_AI_MODEL'] ?? 'gpt-5.1-mini';

        if ($testMode) {
            return $this->answerInTestMode($question);
        }

        try {
            $client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? '');

            // 1) Embedding della domanda
            $embResp = $client->embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $question,
            ]);
            $queryVec = $embResp->embeddings[0]->embedding;

            // 2) Ricerca chunk più simili via cosine_similarity
            $qb = $this->em->createQueryBuilder()
                ->select('c', 'f')
                ->from(DocumentChunk::class, 'c')
                ->join('c.file', 'f')
                ->where('c.embedding IS NOT NULL')
                ->orderBy('cosine_similarity(c.embedding, :vec)', 'DESC')
                ->setMaxResults(5)
                ->setParameter('vec', $queryVec);

            $chunks = $qb->getQuery()->getResult();
            if (!$chunks) {
                return 'Non trovo informazioni rilevanti nei documenti indicizzati.';
            }

            // 3) Costruzione del contesto
            $context = '';
            foreach ($chunks as $chunk) {
                $file = $chunk->getFile();
                $context .= sprintf(
                    "Fonte: %s (chunk %d)\n%s\n\n",
                    $file->getPath(),
                    $chunk->getChunkIndex(),
                    $chunk->getContent()
                );
            }

            // 4) Prompt per il modello Chat
            $system = <<<TXT
Sei un assistente che risponde SOLO usando le informazioni nei documenti forniti.
Se qualcosa non è presente nei documenti, devi dirlo chiaramente.
Rispondi nella stessa lingua della domanda.
TXT;

            $user = <<<TXT
DOCUMENTAZIONE:
{$context}

DOMANDA:
{$question}
TXT;

            $resp = $client->chat()->create([
                'model'    => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $user],
                ],
            ]);

            return $resp->choices[0]->message->content ?? '';
        } catch (\Throwable $e) {
            if ($fallback) {
                return $this->answerInOfflineFallback($question, $e);
            }

            return 'Errore nella chiamata al servizio AI: '.$e->getMessage();
        }
    }

    private function answerInTestMode(string $question): string
    {
        // Esempio:
        // 1) Estrae keyword da $question
        // 2) Esegue query con LIKE su DocumentChunk.content
        // 3) Ritorna elenco di estratti + path dei file
        return '[TEST MODE] Risposta simulata basata solo sui documenti, senza usare OpenAI.';
    }

    private function answerInOfflineFallback(string $question, \Throwable $e): string
    {
        // Comportamento simile a answerInTestMode,
        // includendo un messaggio che segnala l'indisponibilita' del servizio AI.
        return '[OFFLINE FALLBACK] Servizio AI non disponibile. Risposta basata su ricerca locale nei documenti.';
    }
}
```

---

## 3. Command CLI

### 3.1 `IndexDocsCommand`

Command principale per l’indicizzazione (estratto semplificato).

```php
<?php

namespace App\Command;

use App\Entity\DocumentFile;
use App\Entity\DocumentChunk;
use App\Service\DocumentTextExtractor;
use Doctrine\ORM\EntityManagerInterface;
use OpenAI;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:index-docs',
    description: 'Indicizza i documenti di var/knowledge in DocumentFile + DocumentChunk',
)]
class IndexDocsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private DocumentTextExtractor $extractor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE)
            ->addOption('test-mode', null, InputOption::VALUE_NONE)
            ->addOption('force-reindex', null, InputOption::VALUE_NONE)
            ->addOption('path', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun      = (bool) $input->getOption('dry-run');
        $testMode    = (bool) $input->getOption('test-mode');
        $forceReindex = (bool) $input->getOption('force-reindex');
        $pathsFilter = $input->getOption('path');

        $rootDir = \dirname(__DIR__, 2).'/var/knowledge';
        if (!is_dir($rootDir)) {
            $output->writeln('<error>Directory var/knowledge non trovata</error>');
            return Command::FAILURE;
        }

        $client = null;
        if (!$dryRun && !$testMode) {
            $client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? '');
        }

        // Scansione directory, gestione DocumentFile, estrazione testo,
        // splitIntoChunks(), generazione embedding, creazione DocumentChunk...
        // (implementazione completa nel progetto reale)

        return Command::SUCCESS;
    }

    /**
     * Suddivide il testo in chunk di lunghezza massima $maxLen,
     * provando a spezzare a fine frase.
     */
    private function splitIntoChunks(string $text, int $maxLen = 1000): array
    {
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $chunks = [];
        $offset = 0;
        $len    = mb_strlen($text);

        while ($offset < $len) {
            $slice = mb_substr($text, $offset, $maxLen);
            $cut   = mb_strrpos($slice, '.');

            if ($cut === false || $cut < $maxLen * 0.6) {
                $cut = mb_strrpos($slice, ' ') ?: $maxLen;
            }

            $chunk = trim(mb_substr($slice, 0, $cut));
            if ($chunk !== '') {
                $chunks[] = $chunk;
            }

            $offset += $cut;
        }

        return $chunks;
    }
}
```

### 3.2 `ListDocsCommand` e `UnindexFileCommand`

Non sono riportati i sorgenti completi, ma il comportamento è:

- `app:list-docs`
  - legge dalla tabella `document_file`;
  - stampa a console: ID, path, estensione, hash, numero chunk, indexedAt;
  - supporta opzioni di filtro (es. `--path`, `--limit`).

- `app:unindex-file`
  - accetta un pattern (regex) sul `path`;
  - seleziona i `DocumentFile` che matchano il pattern;
  - rimuove i record selezionati;
  - per effetto del `onDelete: 'CASCADE'`, vengono rimossi anche tutti i `DocumentChunk` associati.

---

## 4. Middleware PgVector

### 4.1 `PgvectorIvfflatMiddleware`

Middleware che imposta `ivfflat.probes` a livello di connessione.

```php
<?php

namespace App\Middleware;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(decorates: 'doctrine.dbal.connection')]
class PgvectorIvfflatMiddleware implements Middleware
{
    public function __construct(private int $probes = 10) {}

    public function wrap(Driver $driver): Driver
    {
        return new class($driver, $this->probes) extends AbstractDriverMiddleware {
            public function __construct(Driver $driver, private int $probes)
            {
                parent::__construct($driver);
            }

            public function connect(array $params): Connection
            {
                $connection = parent::connect($params);
                $connection->exec('SET ivfflat.probes = '.$this->probes);

                return $connection;
            }
        };
    }
}
```

### 4.2 Registrazione in `services.yaml`

```yaml
services:
  App\Middleware\PgvectorIvfflatMiddleware:
    arguments:
      $probes: '%env(int:APP_IVFFLAT_PROBES)%'
    tags:
      - 'doctrine.dbal.connection_middleware'
```

---

## 5. Configurazioni Doctrine & pgvector

### 5.1 `config/packages/doctrine.yaml` (estratto)

```yaml
doctrine:
  dbal:
    url: '%env(resolve:DATABASE_URL)%'

    types:
      vector: Partitech\DoctrinePgVector\Type\VectorType

  orm:
    dql:
      string_functions:
        cosine_similarity: Partitech\DoctrinePgVector\Query\CosineSimilarity
        distance: Partitech\DoctrinePgVector\Query\Distance
```

Effetti:

- il tipo Doctrine `vector` viene mappato su `vector(1536)` (o altra dimensione, a seconda delle colonne);
- le funzioni DQL `cosine_similarity()` e `distance()` vengono rese disponibili nelle query Doctrine, mappate sulle funzioni di pgvector.

---

## 6. Chiamate OpenAI

### 6.1 Creazione client

```php
use OpenAI;

$client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? '');
```

### 6.2 Embedding (indicizzazione e query)

```php
$embResp = $client->embeddings()->create([
    'model' => 'text-embedding-3-small',
    'input' => $text, // chunk o domanda
]);

$embedding = $embResp->embeddings[0]->embedding; // array<float>
```

Caratteristiche:

- modello: `text-embedding-3-small`;
- dimensioni embedding: 1536 (configurazione “full”).

### 6.3 Chat completions

```php
$resp = $client->chat()->create([
    'model'    => $_ENV['APP_AI_MODEL'] ?? 'gpt-5.1-mini',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => $userPrompt],
    ],
]);

$answer = $resp->choices[0]->message->content ?? '';
```

---

## 7. Variabili di ambiente specifiche RAG

Riassunto variabili strettamente legate al motore RAG:

```env
# DB con estensione pgvector attiva
DATABASE_URL="postgresql://user:pass@127.0.0.1:5432/ire_db?serverVersion=18&charset=utf8"

# OpenAI
OPENAI_API_KEY=sk-...

# Modello per Chat completions
APP_AI_MODEL=gpt-5.1-mini

# Modalità di esecuzione
APP_AI_TEST_MODE=false
APP_AI_OFFLINE_FALLBACK=true

# Parametro ivfflat.probes
APP_IVFFLAT_PROBES=20
```

---

_Fine Documento 2 — Codice & Configurazioni Motore AI RAG_
