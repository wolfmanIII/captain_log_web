<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aggiunge short_description a local_law';
    }

    public function up(Schema $schema): void
    {
        try {
            $this->addSql('ALTER TABLE local_law ADD short_description VARCHAR(255) DEFAULT NULL');
        } catch (\Throwable $e) {
            // ignore if already present (SQLite or previous attempt)
        }
    }

    public function down(Schema $schema): void
    {
        try {
            $this->addSql('ALTER TABLE local_law DROP COLUMN short_description');
        } catch (\Throwable $e) {
            // ignore if column missing
        }
    }
}
