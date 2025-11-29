### 1. Symfony
#### Dipendenze aggiuntive da installare
```bash
composer require \
    smalot/pdfparser \
    phpoffice/phpword \
    openai-php/client \
    partitech/doctrine-pgvector
```
---
### 2. Open AI
#### Nel file .env.local metti la chiave:
```env
OPENAI_API_KEY=sk-...
```
---
### 3. PostgreSQL + pgvector + Doctrine
#### Installare postgres + pgvector
```bash
sudo apt install postgresql-18 postgresql-18-pgvector
```
#### Nel database PostgreSQL (una volta sola)
sql
```sql
CREATE EXTENSION IF NOT EXISTS vector;
```
---
### In config/packages/doctrine.yaml aggiungi il tipo e le funzioni DQL:
yaml
```yaml
doctrine:
  dbal:
    # ... il tuo config solito (url, ecc.)
    types:
      vector: Partitech\DoctrinePgVector\Type\VectorType

  orm:
    # ...
    dql:
      string_functions:
        cosine_similarity: Partitech\DoctrinePgVector\Query\CosineSimilarity
        distance: Partitech\DoctrinePgVector\Query\Distance

```
### Indicizzatore - esempi di utilizzo
