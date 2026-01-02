<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea le tabelle income_charter_details e income_subsidy_details (FK 1:1 con income)';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        // SQLite e altre: creiamo le tabelle esplicitamente
        $this->addSql(<<<'SQL'
CREATE TABLE income_charter_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    income_id INTEGER NOT NULL,
    area_or_route VARCHAR(255) DEFAULT NULL,
    purpose VARCHAR(255) DEFAULT NULL,
    manifest_summary CLOB DEFAULT NULL,
    payment_terms CLOB DEFAULT NULL,
    deposit NUMERIC(11, 2) DEFAULT NULL,
    extras CLOB DEFAULT NULL,
    damage_terms CLOB DEFAULT NULL,
    cancellation_terms CLOB DEFAULT NULL,
    CONSTRAINT FK_CHAR_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_CHARTER_DETAILS_INCOME ON income_charter_details (income_id)');

        $this->addSql(<<<'SQL'
CREATE TABLE income_subsidy_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    income_id INTEGER NOT NULL,
    program_ref VARCHAR(100) DEFAULT NULL,
    origin VARCHAR(255) DEFAULT NULL,
    destination VARCHAR(255) DEFAULT NULL,
    service_level VARCHAR(255) DEFAULT NULL,
    subsidy_amount NUMERIC(11, 2) DEFAULT NULL,
    payment_terms CLOB DEFAULT NULL,
    milestones CLOB DEFAULT NULL,
    reporting_requirements CLOB DEFAULT NULL,
    non_compliance_terms CLOB DEFAULT NULL,
    proof_requirements CLOB DEFAULT NULL,
    cancellation_terms CLOB DEFAULT NULL,
    CONSTRAINT FK_SUBSIDY_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_SUBSIDY_DETAILS_INCOME ON income_subsidy_details (income_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS income_charter_details');
        $this->addSql('DROP TABLE IF EXISTS income_subsidy_details');
    }
}
