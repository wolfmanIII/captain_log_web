<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107114500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea le tabelle income_services_details e income_insurance_details (FK 1:1 con income)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE income_services_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    income_id INTEGER NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    vessel_id VARCHAR(255) DEFAULT NULL,
    service_type VARCHAR(255) DEFAULT NULL,
    requested_by VARCHAR(255) DEFAULT NULL,
    start_day INTEGER DEFAULT NULL,
    start_year INTEGER DEFAULT NULL,
    end_day INTEGER DEFAULT NULL,
    end_year INTEGER DEFAULT NULL,
    work_summary CLOB DEFAULT NULL,
    parts_materials CLOB DEFAULT NULL,
    risks CLOB DEFAULT NULL,
    payment_terms CLOB DEFAULT NULL,
    extras CLOB DEFAULT NULL,
    total NUMERIC(11, 2) DEFAULT NULL,
    liability_limit NUMERIC(11, 2) DEFAULT NULL,
    cancellation_terms CLOB DEFAULT NULL,
    CONSTRAINT FK_SERVICES_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_SERVICES_DETAILS_INCOME ON income_services_details (income_id)');

        $this->addSql(<<<'SQL'
CREATE TABLE income_insurance_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    income_id INTEGER NOT NULL,
    claim_id VARCHAR(100) DEFAULT NULL,
    policy_number VARCHAR(100) DEFAULT NULL,
    incident_ref VARCHAR(100) DEFAULT NULL,
    incident_day INTEGER DEFAULT NULL,
    incident_year INTEGER DEFAULT NULL,
    incident_location VARCHAR(255) DEFAULT NULL,
    incident_cause VARCHAR(255) DEFAULT NULL,
    loss_type VARCHAR(255) DEFAULT NULL,
    verified_loss NUMERIC(11, 2) DEFAULT NULL,
    payout_amount NUMERIC(11, 2) DEFAULT NULL,
    deductible NUMERIC(11, 2) DEFAULT NULL,
    payment_terms CLOB DEFAULT NULL,
    acceptance_effect CLOB DEFAULT NULL,
    subrogation_terms CLOB DEFAULT NULL,
    coverage_notes CLOB DEFAULT NULL,
    CONSTRAINT FK_INSURANCE_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_INSURANCE_DETAILS_INCOME ON income_insurance_details (income_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS income_services_details');
        $this->addSql('DROP TABLE IF EXISTS income_insurance_details');
    }
}
