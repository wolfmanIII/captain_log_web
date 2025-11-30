# Motore AI RAG — Documentazione Architetturale

Versione: 1.1  
Progetto: **IA RAG Engine(IRE)** (Symfony 7.3)  
Ambiente target: Postgres 18 + pgvector + OpenAI API

---

## 0. Scopo del documento

Questo documento descrive **solo** il sotto–sistema RAG (Retrieval‑Augmented Generation) dell’applicazione:

- architettura logica e componenti;
- flussi di **indicizzazione** dei documenti;
- flussi di **query / chatbot**;
- modalità di esecuzione speciali (test mode, offline fallback);
- parametri di configurazione rilevanti per il RAG.

Tutto ciò che riguarda UI, routing HTTP generico, altre feature dell’app non è incluso.

---

## 1. Panoramica del motore RAG

### 1.1 Obiettivo

Il motore RAG permette all’applicazione di:

1. indicizzare documenti testuali (PDF, Markdown, ODT, DOCX) contenuti in `var/knowledge/`;
2. suddividerli in spezzoni (“chunk”) e associare ad ogni chunk un **embedding vettoriale** tramite OpenAI;
3. memorizzare questi embedding nel database Postgres (estensione `pgvector`);
4. al momento della query:
   - calcolare l’embedding della domanda;
   - cercare i chunk più simili semanticamente;
   - assemblare un contesto testuale;
   - inviare il tutto a un modello Chat OpenAI;
   - restituire una risposta che **deve basarsi solo sui documenti**.

### 1.2 Componenti principali

- **Storage (DB)**
  - Tabella `document_file`
    - Metadati sul file fisico.
  - Tabella `document_chunk`
    - Spezzoni di testo + colonna `embedding vector(1536)`.

- **Indicizzazione (CLI)**
  - `app:index-docs`  
    Scansione `var/knowledge`, estrazione testo, split in chunk, embedding tramite OpenAI, salvataggio in DB.
  - `app:list-docs`  
    Elenco ed ispezione dei `DocumentFile` indicizzati.
  - `app:unindex-file`  
    Cancellazione selettiva o massiva dei file indicizzati (e relativi chunk).

- **Runtime (Chatbot RAG)**
  - `ChatbotService`  
    Incapsula il flusso:
    - calcolo embedding domanda;
    - query pgvector via `cosine_similarity`;
    - costruzione del contesto;
    - chiamata al modello Chat;
    - gestione modalità test/offline.

- **Ottimizzazioni DB**
  - Tipo Doctrine personalizzato `vector` (da `partitech/doctrine-pgvector`);
  - Funzione DQL `cosine_similarity()` che mappa sulla stessa funzione Postgres;
  - Middleware `PgvectorIvfflatMiddleware` che imposta il parametro `ivfflat.probes` ad ogni nuova connessione.

---

## 2. Flusso di Indicizzazione

### 2.1 Entry point

Comando CLI principale:

```bash
php bin/console app:index-docs [opzioni]
```

Opzioni chiave:

- `--dry-run`  
  Simula l’indicizzazione: nessun salvataggio su DB, nessuna chiamata OpenAI.
- `--test-mode`  
  Usa embedding “finti” generati localmente, senza chiamare OpenAI.
- `--force-reindex`  
  Forza la reindicizzazione anche se l’hash del file non è cambiato.
- `--path=...` (ripetibile)  
  Limita la scansione a sottopercorsi specifici dentro `var/knowledge`.

### 2.2 Directory sorgente

La knowledge base è localizzata in:

```text
var/knowledge/
    manuali/
    log/
    ...
```

Il comando:

1. verifica che `var/knowledge` esista e sia una directory;
2. scandisce ricorsivamente tutte le sottocartelle;
3. filtra directory non rilevanti (es. `.git`, `node_modules`, ecc.);
4. esclude nomi di file noti non utili (es. `.DS_Store`);
5. considera solo estensioni supportate: `pdf`, `md`, `odt`, `docx`.

### 2.3 Hash dei file e gestione DocumentFile

Per ogni file fisico trovato:

1. viene calcolato il **path relativo** rispetto a `var/knowledge`  
   (es.: `manuali/helix.md`);
2. viene calcolato un **hash SHA‑256** del contenuto:

   ```php
   $relPath  = substr($filePath, strlen($rootDir) + 1);
   $fileHash = hash_file('sha256', $filePath);
   ```

3. viene cercato nel DB un `DocumentFile` con lo stesso `path`.

Comportamento:

- Se **esiste** e l’hash è **identico** e non è attivo `--force-reindex`:
  - il file viene **saltato** (già indicizzato e non modificato).
- Se **esiste** ma l’hash è **diverso**, oppure è attivo `--force-reindex`:
  - vengono rimossi i `DocumentChunk` associati a quel `DocumentFile`;
  - il record `DocumentFile` viene aggiornato con il nuovo hash;
  - viene azzerato e rigenerato l’indice dei chunk.
- Se **non esiste**:
  - viene creato un nuovo `DocumentFile` con:
    - `path` (string),
    - `extension` (string),
    - `hash` (SHA‑256),
    - `indexedAt` (data/ora corrente),
    - lista vuota di chunk.

### 2.4 Estrazione testo

Responsabile: servizio `DocumentTextExtractor`.

- Seleziona l’estrattore in base all’estensione:
  - `PDF` → parser PDF (Smalot PdfParser);
  - `MD` → lettura file + pulizia sintassi di base;
  - `ODT` → estrazione testo dal contenuto ODT;
  - `DOCX` → PhpWord.

Passi generici:

1. lettura del file;
2. estrazione del testo “grezzo”;
3. normalizzazione degli spazi:

   ```php
   $text = preg_replace('/\s+/u', ' ', $text);
   $text = trim($text);
   ```

4. rimozione/normalizzazione di emoji e caratteri speciali, per evitare problemi nelle chiamate OpenAI e nel DB.

Se il testo risultante è vuoto → il file non viene indicizzato (nessun chunk creato).

### 2.5 Suddivisione in chunk

Metodo concettuale:

```php
/**
 * Suddivide il testo in chunk di dimensione massima $maxLen,
 * cercando di spezzare preferibilmente a fine frase.
 */
private function splitIntoChunks(string $text, int $maxLen = 1000): array
```

Algoritmo:

1. collassa whitespace e trim;
2. mentre ci sono caratteri rimanenti:
   - prende una “finestra” di lunghezza `maxLen`;
   - prova a trovare l’ultimo `.` nella finestra:
     - se c’è e non è troppo vicino all’inizio → spezza lì;
   - altrimenti, cerca l’ultimo spazio;
   - se anche lo spazio non c’è → taglio secco a `maxLen`;
3. aggiunge il chunk (trim) alla lista;
4. avanza l’offset nel testo complessivo.

Vantaggi:

- chunk di dimensione approssimativamente controllata (~1000 caratteri);
- maggiore probabilità di spezzare a confini semantici (fine frase), migliorando qualità degli embedding.

### 2.6 Generazione embedding (OpenAI)

Condizioni:

- NON deve essere attivo `--dry-run`;
- NON deve essere attivo `--test-mode` (o `APP_AI_TEST_MODE=true`).

In questi casi, il comando:

1. inizializza un client OpenAI:

   ```php
   $client = OpenAI::client($_ENV['OPENAI_API_KEY'] ?? '');
   ```

2. per ogni chunk genera un embedding:

   ```php
   $embResp = $client->embeddings()->create([
       'model' => 'text-embedding-3-small',
       'input' => $chunkText,
   ]);

   $embedding = $embResp->embeddings[0]->embedding; // float[1536]
   ```

3. gestisce eventuali errori:

   - se `APP_AI_OFFLINE_FALLBACK=true`:
     - viene creato un embedding fittizio deterministico basato sul testo (per mantenere compatibilità con pgvector);
   - altrimenti:
     - il chunk viene saltato e l’errore loggato a console.

### 2.7 Persistenza dei chunk

Per ogni chunk valido:

- viene creato un nuovo `DocumentChunk` con:

  - riferimento al `DocumentFile` corrente;
  - `chunkIndex` progressivo (0, 1, 2, …);
  - `content` = testo del chunk;
  - `embedding` = array di float.

- l’entità viene persistita:

  ```php
  $chunk = (new DocumentChunk())
      ->setFile($fileEntity)
      ->setChunkIndex($index)
      ->setContent($chunkText)
      ->setEmbedding($embedding);

  $this->em->persist($chunk);
  ```

L’operazione può essere ottimizzata effettuando il flush in batch, per limitare il numero di transazioni.

### 2.8 Index pgvector

Sul campo `embedding` (tipo `vector(1536)`) viene creato un indice `ivfflat`:

```sql
CREATE EXTENSION IF NOT EXISTS vector;

CREATE INDEX IF NOT EXISTS document_chunk_embedding_ivfflat_idx
ON document_chunk
USING ivfflat (embedding vector_cosine_ops)
WITH (lists = 100);
```

Questo indice, combinato con `ivfflat.probes`, migliora notevolmente la performance delle ricerche di similitudine.

---

## 3. Flusso di Query (Chatbot RAG)

### 3.1 Entry point applicativo

Il flusso runtime è incapsulato nel servizio:

```php
App\Service\ChatbotService
```

Metodo pubblico principale:

```php
public function ask(string $question): string
```

Tipicamente invocato da un Controller HTTP che riceve la domanda da form o da chiamata AJAX.

### 3.2 Modalità operative

Il servizio legge alcune variabili di ambiente:

- `APP_AI_TEST_MODE`
- `APP_AI_OFFLINE_FALLBACK`
- `APP_AI_MODEL` (default: `gpt-5.1-mini`)

Comportamento:

- se `APP_AI_TEST_MODE=true` → attiva modalità **test** (solo DB, niente OpenAI);
- altrimenti, funziona in modalità **normale** con OpenAI;
- in caso di errore, se `APP_AI_OFFLINE_FALLBACK=true`, tenta modalità **offline fallback** (solo DB, niente OpenAI).

### 3.3 Flusso normale (OpenAI attivo)

Passi principali:

1. **Calcolo embedding domanda**

   ```php
   $embResp = $client->embeddings()->create([
       'model' => 'text-embedding-3-small',
       'input' => $question,
   ]);

   $queryVec = $embResp->embeddings[0]->embedding;
   ```

2. **Ricerca chunk simili (pgvector)**

   Si usa una query Doctrine con DQL personalizzato:

   ```php
   $qb = $this->em->createQueryBuilder()
       ->select('c', 'f')
       ->from(DocumentChunk::class, 'c')
       ->join('c.file', 'f')
       ->where('c.embedding IS NOT NULL')
       ->orderBy('cosine_similarity(c.embedding, :vec)', 'DESC')
       ->setMaxResults(5)
       ->setParameter('vec', $queryVec);
   ```

   Il parametro `:vec` è un array PHP di float (lunghezza 1536), mappato sul tipo `vector` Postgres.

   Se nessun chunk viene trovato → il servizio ritorna una risposta “vuota” del tipo:

   > Non trovo informazioni rilevanti nei documenti indicizzati.

3. **Costruzione del contesto**

   Per ogni chunk trovato:

   ```php
   $context = '';

   foreach ($chunks as $chunk) {
       $file = $chunk->getFile();
       $context .= sprintf(
           "Fonte: %s (chunk %d)
%s

",
           $file->getPath(),
           $chunk->getChunkIndex(),
           $chunk->getContent()
       );
   }
   ```

   Il contesto finale è un’unica stringa con:

   - path file;
   - indice chunk;
   - testo del chunk.

4. **Prompt al modello Chat**

   - **System prompt** (comportamento rigido):

     ```text
     Sei un assistente che risponde SOLO usando le informazioni nei documenti forniti.
     Se qualcosa non è presente nei documenti, devi dirlo chiaramente.
     Rispondi nella stessa lingua della domanda.
     ```

   - **User prompt** (contesto + domanda):

     ```text
     DOCUMENTAZIONE:
     {context}

     DOMANDA:
     {question}
     ```

5. **Chiamata OpenAI Chat**

   ```php
   $resp = $client->chat()->create([
       'model'    => $model, // es. gpt-5.1-mini
       'messages' => [
           ['role' => 'system', 'content' => $system],
           ['role' => 'user',   'content' => $user],
       ],
   ]);

   $answer = $resp->choices[0]->message->content ?? '';
   ```

6. **Ritorno al Controller**

   Il `Controller` si limita ad incapsulare la stringa in una Response (HTML o JSON).

### 3.4 Sequence Diagram — Query

```text
Actor: Utente Web
System: Controller Symfony + ChatbotService + Postgres + OpenAI

Utente → Controller: invio domanda "Q"
Controller → ChatbotService: ask("Q")

ChatbotService → OpenAI: embeddings(model=text-embedding-3-small, input=Q)
OpenAI → ChatbotService: vettore queryVec[1536]

ChatbotService → DB:
  SELECT c,f
  FROM document_chunk c JOIN document_file f
  ORDER BY cosine_similarity(c.embedding, queryVec) DESC
  LIMIT 5
DB → ChatbotService: lista chunk

ChatbotService:
  costruisce context = somma dei chunk
  crea system+user prompt

ChatbotService → OpenAI:
  chat(model=APP_AI_MODEL, messages=[system, user])
OpenAI → ChatbotService: risposta R

ChatbotService → Controller: R
Controller → Utente: renderizza R
```

---

## 4. Modalità Test e Offline Fallback

### 4.1 Test Mode (`APP_AI_TEST_MODE=true`)

Obiettivo: consentire test rapidi senza costi e senza dipendenza dalla rete.

Effetti:

- `IndexDocsCommand`
  - non chiama OpenAI per gli embedding → usa embedding fittizi (es. pseudo-random deterministici) oppure salta del tutto il popolamento della colonna, a seconda dell’implementazione.
- `ChatbotService`
  - non chiama OpenAI;
  - esegue una ricerca **testuale** (es. via `LIKE` su `content`) per trovare chunk che contengono parole chiave della domanda;
  - restituisce una risposta sintetica che elenca i documenti/estratti pertinenti, senza generazione “intelligente”.

### 4.2 Offline Fallback (`APP_AI_OFFLINE_FALLBACK=true`)

Obiettivo: rendere il sistema sfruttabile anche se OpenAI è temporaneamente non disponibile.

Effetti:

- in indicizzazione:
  - se una richiesta a `embeddings()->create()` fallisce, si genera un embedding fittizio locale (stessa dimensione 1536) in modo da poter utilizzare comunque pgvector;
- in query:
  - se qualsiasi chiamata a OpenAI fallisce (embedding domanda o chat), si passa ad un flusso analogo al test mode:
    - ricerca testuale (LIKE) su `content`;
    - restituzione di estratti di testo “grezzi” accompagnati da un messaggio che indica l’indisponibilità del servizio AI.

---

## 5. Configurazioni e Parametri

### 5.1 Variabili di ambiente

| Variabile                  | Default        | Uso                                     | Note                                               |
|---------------------------|----------------|-----------------------------------------|----------------------------------------------------|
| `DATABASE_URL`            | —              | Doctrine / Postgres                     | Deve puntare a Postgres con estensione `vector`.   |
| `OPENAI_API_KEY`          | —              | IndexDocsCommand, ChatbotService        | Chiave privata OpenAI.                            |
| `APP_AI_MODEL`            | `gpt-5.1-mini` | ChatbotService                          | Modello Chat; modificabile (es. `gpt-4.1-mini`).   |
| `APP_AI_TEST_MODE`        | `false`        | IndexDocsCommand, ChatbotService        | Se `true`, disabilita tutte le chiamate OpenAI.    |
| `APP_AI_OFFLINE_FALLBACK` | `true`         | IndexDocsCommand, ChatbotService        | Se `true`, abilita modalità degradata locale.      |
| `APP_IVFFLAT_PROBES`      | `10` (esempio) | PgvectorIvfflatMiddleware               | Controlla trade-off qualità/velocità ricerche.     |

Esempio `.env.local`:

```env
DATABASE_URL="postgresql://user:pass@127.0.0.1:5432/ire_db?serverVersion=18&charset=utf8"

OPENAI_API_KEY=sk-xxxx
APP_AI_MODEL=gpt-5.1-mini
APP_AI_TEST_MODE=false
APP_AI_OFFLINE_FALLBACK=true

APP_IVFFLAT_PROBES=20
```

### 5.2 Middleware `PgvectorIvfflatMiddleware`

Il middleware effettua, per ogni nuova connessione DB:

```sql
SET ivfflat.probes = <APP_IVFFLAT_PROBES>;
```

Valori tipici:

- 5–10 → più veloce, meno accurato;
- 20–50 → più accurato, più lento.

---

## 6. Manutenzione della Knowledge Base

### 6.1 Elenco documenti indicizzati (`app:list-docs`)

Permette di ispezionare il contenuto di `document_file` (path, hash, estensione, numero di chunk).

Esempi:

```bash
php bin/console app:list-docs
php bin/console app:list-docs --path=manuali
php bin/console app:list-docs --limit=200
```

### 6.2 Rimozione documenti indicizzati (`app:unindex-file`)

Permette di cancellare uno o più file indicizzati, tramite regex sul `path`.

Esempi:

```bash
# un singolo file:
php bin/console app:unindex-file "manuali/helix.md"

# tutti i file in una cartella:
php bin/console app:unindex-file "^manuali/"

# tutti i PDF:
php bin/console app:unindex-file "\.pdf$"

# reset totale dell'indice:
php bin/console app:unindex-file ".*"
```

---

## 7. Considerazioni su sicurezza e privacy

- I documenti in `var/knowledge` **non devono** contenere informazioni che non si desidera rendere disponibili tramite il chatbot.
- Il sistema RAG non aggiunge di per sé permessi a livello di documento: tutti i documenti indicizzati sono potenzialmente utilizzati per qualunque domanda.
- Per scenari multi–tenant o multi–ruolo, è necessario:
  - separare fisicamente le collection (DB diversi o tabelle separate);
  - o introdurre filtri a livello di query (es. `DocumentFile` marcati con un owner / scope).

---

_Fine Documento 1 — Architettura Motore AI RAG_
