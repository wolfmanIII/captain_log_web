<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Aggiunge il campo payment_terms a income_contract_details.
 */
final class Version20260102161341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aggiunge payment_terms alla tabella income_contract_details';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE income_contract_details ADD COLUMN payment_terms CLOB DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // SQLite non supporta DROP COLUMN; per revert creare manualmente una tabella senza payment_terms.
    }
}
