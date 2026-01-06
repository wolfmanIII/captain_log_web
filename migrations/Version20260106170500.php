<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260106170500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aggiunge la colonna detail_items a cost (migrazione minimale compatibile SQLite)';
    }

    public function up(Schema $schema): void
    {
        // Nota: niente commenti inline per evitare errori SQLite "incomplete input".
        $this->addSql('ALTER TABLE cost ADD COLUMN detail_items CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // SQLite non supporta DROP COLUMN; nessuna azione.
    }
}
