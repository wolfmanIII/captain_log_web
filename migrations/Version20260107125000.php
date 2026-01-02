<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107125000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea le tabelle income_prize_details e income_contract_details (FK 1:1 con income)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE income_prize_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    income_id INTEGER NOT NULL,
    prize_id VARCHAR(100) DEFAULT NULL,
    case_ref VARCHAR(100) DEFAULT NULL,
    jurisdiction VARCHAR(255) DEFAULT NULL,
    legal_basis CLOB DEFAULT NULL,
    prize_description CLOB DEFAULT NULL,
    estimated_value NUMERIC(11, 2) DEFAULT NULL,
    disposition VARCHAR(255) DEFAULT NULL,
    payment_terms CLOB DEFAULT NULL,
    share_split CLOB DEFAULT NULL,
    award_trigger CLOB DEFAULT NULL,
    CONSTRAINT FK_PRIZE_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_PRIZE_DETAILS_INCOME ON income_prize_details (income_id)');

        $this->addSql(<<<'SQL'
CREATE TABLE income_contract_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    income_id INTEGER NOT NULL,
    job_type VARCHAR(255) DEFAULT NULL,
    location CLOB DEFAULT NULL,
    objective CLOB DEFAULT NULL,
    success_condition CLOB DEFAULT NULL,
    start_day INTEGER DEFAULT NULL,
    start_year INTEGER DEFAULT NULL,
    deadline_day INTEGER DEFAULT NULL,
    deadline_year INTEGER DEFAULT NULL,
    bonus NUMERIC(11, 2) DEFAULT NULL,
    expenses_policy CLOB DEFAULT NULL,
    deposit NUMERIC(11, 2) DEFAULT NULL,
    restrictions CLOB DEFAULT NULL,
    confidentiality_level CLOB DEFAULT NULL,
    failure_terms CLOB DEFAULT NULL,
    cancellation_terms CLOB DEFAULT NULL,
    CONSTRAINT FK_CONTRACT_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_CONTRACT_DETAILS_INCOME ON income_contract_details (income_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS income_prize_details');
        $this->addSql('DROP TABLE IF EXISTS income_contract_details');
    }
}
