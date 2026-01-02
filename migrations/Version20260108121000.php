<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rimuove la colonna jurisdiction da income_prize_details (si usa Income.localLaw).
 */
final class Version20260108121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop jurisdiction da income_prize_details';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__income_prize_details AS SELECT id, income_id, case_ref, legal_basis, prize_description, estimated_value, disposition, payment_terms, share_split, award_trigger FROM income_prize_details');
        $this->addSql('DROP TABLE income_prize_details');
        $this->addSql('CREATE TABLE income_prize_details (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, income_id INTEGER NOT NULL, case_ref VARCHAR(100) DEFAULT NULL, legal_basis CLOB DEFAULT NULL, prize_description CLOB DEFAULT NULL, estimated_value NUMERIC(11, 2) DEFAULT NULL, disposition VARCHAR(255) DEFAULT NULL, payment_terms CLOB DEFAULT NULL, share_split CLOB DEFAULT NULL, award_trigger CLOB DEFAULT NULL, CONSTRAINT FK_PRIZE_INCOME FOREIGN KEY (income_id) REFERENCES income (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO income_prize_details (id, income_id, case_ref, legal_basis, prize_description, estimated_value, disposition, payment_terms, share_split, award_trigger) SELECT id, income_id, case_ref, legal_basis, prize_description, estimated_value, disposition, payment_terms, share_split, award_trigger FROM __temp__income_prize_details');
        $this->addSql('DROP TABLE __temp__income_prize_details');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PRIZE_INCOME ON income_prize_details (income_id)');
    }

    public function down(Schema $schema): void
    {
        // Revert non supportato su SQLite senza ricreare la colonna; ripristinare manualmente se necessario.
    }
}
