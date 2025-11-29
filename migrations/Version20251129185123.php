<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129185123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document_chunk (id SERIAL NOT NULL, path VARCHAR(255) NOT NULL, extension VARCHAR(10) NOT NULL, chunk_index INT NOT NULL, content TEXT NOT NULL, indexed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, file_hash VARCHAR(64) NOT NULL, embedding vector(1024) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_document_chunk_path ON document_chunk (path)');
        $this->addSql('COMMENT ON COLUMN document_chunk.indexed_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE document_chunk');
    }
}
