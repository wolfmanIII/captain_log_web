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
        $offlineFallbackEnabled =
            ($_ENV['APP_AI_OFFLINE_FALLBACK'] ?? 'true') === 'true';

        if ($testMode) {
            return $this->answerInTestMode($question);
        }

        try {
            $client = OpenAI::client($_ENV['OPENAI_API_KEY']);
            $model  = $_ENV['APP_AI_MODEL'] ?? 'gpt-5.1-mini';

            // 1) Embedding della domanda
            $embResp = $client->embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $question,
                // default 1536 dimensioni
            ]);
            $queryVec = $embResp->embeddings[0]->embedding;

            // 2) Recupero chunk più simili (top 5) usando cosine_similarity
            $qb = $this->em->createQueryBuilder()
                ->select('c', 'f')
                ->from(DocumentChunk::class, 'c')
                ->join('c.file', 'f')
                ->where('c.embedding IS NOT NULL')
                ->orderBy('cosine_similarity(c.embedding, :vec)', 'DESC')
                ->setMaxResults(5)
                ->setParameter('vec', $queryVec);

            /** @var DocumentChunk[] $chunks */
            $chunks = $qb->getQuery()->getResult();

            if (!$chunks) {
                return 'Non trovo informazioni rilevanti nei documenti indicizzati.';
            }

            $context = '';
            foreach ($chunks as $chunk) {
                $file = $chunk->getFile();
                $context .= "Fonte: ".$file->getPath()." (chunk ".$chunk->getChunkIndex().")\n";
                $context .= $chunk->getContent()."\n\n";
            }

            $system = <<<TXT
Sei un assistente che risponde SOLO usando le informazioni nei documenti forniti.
Se la risposta non è presente nei documenti, devi dire chiaramente che non trovi
l'informazione nei documenti indicizzati. Rispondi nella stessa lingua della domanda.
TXT;

            $user = <<<TXT
DOCUMENTAZIONE:
{$context}

DOMANDA:
{$question}
TXT;

            $resp = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'max_tokens' => 400,
            ]);

            return $resp->choices[0]->message->content ?? '';
        } catch (\Throwable $e) {
            if ($offlineFallbackEnabled) {
                return $this->answerInOfflineFallback($question, $e);
            }

            return 'Errore nella chiamata al servizio AI: '.$e->getMessage();
        }
    }

    private function answerInTestMode(string $question): string
    {
        $keywords = $this->buildKeywords($question);

        $qb = $this->em->createQueryBuilder()
            ->select('c', 'f')
            ->from(DocumentChunk::class, 'c')
            ->join('c.file', 'f')
            ->setMaxResults(5);

        if ($keywords) {
            $expr = $qb->expr();
            $orX  = $expr->orX();

            foreach ($keywords as $idx => $kw) {
                $paramName = 'k'.$idx;
                $orX->add($expr->like('LOWER(c.content)', ':'.$paramName));
                $qb->setParameter($paramName, '%'.$kw.'%');
            }

            $qb->where($orX);
        } else {
            $qb->where('LOWER(c.content) LIKE :q')
               ->setParameter('q', '%'.mb_strtolower($question).'%');
        }

        /** @var DocumentChunk[] $chunks */
        $chunks = $qb->getQuery()->getResult();

        if (!$chunks) {
            return "[TEST MODE] Nessun documento sembra contenere la query.\n\nDomanda: ".$question;
        }

        $out = "[TEST MODE] Non sto chiamando OpenAI.\n";
        $out .= "Questi sono alcuni estratti che sembrano rilevanti:\n\n";

        foreach ($chunks as $chunk) {
            $file    = $chunk->getFile();
            $preview = mb_substr($chunk->getContent(), 0, 300);
            $out .= "- Fonte: ".$file->getPath()." (chunk ".$chunk->getChunkIndex().")\n";
            $out .= "  Estratto: ".str_replace("\n", ' ', $preview)."…\n\n";
        }

        return $out;
    }

    private function answerInOfflineFallback(string $question, \Throwable $e): string
    {
        $keywords = $this->buildKeywords($question);

        $qb = $this->em->createQueryBuilder()
            ->select('c', 'f')
            ->from(DocumentChunk::class, 'c')
            ->join('c.file', 'f')
            ->setMaxResults(5);

        if ($keywords) {
            $expr = $qb->expr();
            $orX  = $expr->orX();

            foreach ($keywords as $idx => $kw) {
                $paramName = 'k'.$idx;
                $orX->add($expr->like('LOWER(c.content)', ':'.$paramName));
                $qb->setParameter($paramName, '%'.$kw.'%');
            }

            $qb->where($orX);
        } else {
            $qb->where('LOWER(c.content) LIKE :q')
               ->setParameter('q', '%'.mb_strtolower($question).'%');
        }

        /** @var DocumentChunk[] $chunks */
        $chunks = $qb->getQuery()->getResult();

        if (!$chunks) {
            return "Il servizio AI non è raggiungibile e non trovo nulla nei documenti locali per la tua domanda.\n"
                 . "Dettaglio tecnico: ".$e->getMessage();
        }

        $out = "Il servizio AI non è raggiungibile in questo momento, "
             . "ma ho trovato alcuni estratti nei documenti locali:\n\n";

        foreach ($chunks as $chunk) {
            $file    = $chunk->getFile();
            $preview = mb_substr($chunk->getContent(), 0, 300);
            $out .= "- Fonte: ".$file->getPath()." (chunk ".$chunk->getChunkIndex().")\n";
            $out .= "  Estratto: ".str_replace("\n", ' ', $preview)."…\n\n";
        }

        $out .= "\n(Dettaglio tecnico: ".$e->getMessage().")";

        return $out;
    }

    private function buildKeywords(string $text): array
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        if ($text === null) {
            return [];
        }

        $parts = preg_split('/\s+/', trim($text));
        if (!$parts) {
            return [];
        }

        $keywords = [];
        foreach ($parts as $p) {
            if (mb_strlen($p) < 3) {
                continue;
            }
            $keywords[] = $p;
        }

        return array_values(array_unique($keywords));
    }
}
