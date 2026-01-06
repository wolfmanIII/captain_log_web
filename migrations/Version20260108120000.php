<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Rimuove gli ID duplicati nei dettagli Income (si riusa Income.code).
 */
final class Version20260108120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop claimId/prizeId/receiptId/vesselId dai dettagli Income per usare solo Income.code';
    }

    public function up(Schema $schema): void
    {
        // income_salvage_details: drop claim_id
        $this->addSql('CREATE TEMPORARY TABLE __temp__income_salvage_details AS SELECT id, income_id, case_ref, source, site_location, recovered_items_summary, qty_value, hazards, payment_terms, split_terms, rights_basis, award_trigger, dispute_process FROM income_salvage_details');
        $this->addSql('DROP TABLE income_salvage_details');
        $this->addSql('CREATE TABLE income_salvage_details (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, income_id INTEGER NOT NULL, case_ref VARCHAR(100) DEFAULT NULL, source VARCHAR(100) DEFAULT NULL, site_location VARCHAR(255) DEFAULT NULL, recovered_items_summary CLOB DEFAULT NULL, qty_value NUMERIC(11, 2) DEFAULT NULL, hazards CLOB DEFAULT NULL, payment_terms CLOB DEFAULT NULL, split_terms CLOB DEFAULT NULL, rights_basis CLOB DEFAULT NULL, award_trigger CLOB DEFAULT NULL, dispute_process CLOB DEFAULT NULL, CONSTRAINT FK_SALVAGE_INCOME FOREIGN KEY (income_id) REFERENCES income (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO income_salvage_details (id, income_id, case_ref, source, site_location, recovered_items_summary, qty_value, hazards, payment_terms, split_terms, rights_basis, award_trigger, dispute_process) SELECT id, income_id, case_ref, source, site_location, recovered_items_summary, qty_value, hazards, payment_terms, split_terms, rights_basis, award_trigger, dispute_process FROM __temp__income_salvage_details');
        $this->addSql('DROP TABLE __temp__income_salvage_details');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SALVAGE_INCOME ON income_salvage_details (income_id)');

        // income_prize_details: drop prize_id
        $this->addSql('CREATE TEMPORARY TABLE __temp__income_prize_details AS SELECT id, income_id, case_ref, jurisdiction, legal_basis, prize_description, estimated_value, disposition, payment_terms, share_split, award_trigger FROM income_prize_details');
        $this->addSql('DROP TABLE income_prize_details');
        $this->addSql('CREATE TABLE income_prize_details (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, income_id INTEGER NOT NULL, case_ref VARCHAR(100) DEFAULT NULL, jurisdiction VARCHAR(255) DEFAULT NULL, legal_basis CLOB DEFAULT NULL, prize_description CLOB DEFAULT NULL, estimated_value NUMERIC(11, 2) DEFAULT NULL, disposition VARCHAR(255) DEFAULT NULL, payment_terms CLOB DEFAULT NULL, share_split CLOB DEFAULT NULL, award_trigger CLOB DEFAULT NULL, CONSTRAINT FK_PRIZE_INCOME FOREIGN KEY (income_id) REFERENCES income (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO income_prize_details (id, income_id, case_ref, jurisdiction, legal_basis, prize_description, estimated_value, disposition, payment_terms, share_split, award_trigger) SELECT id, income_id, case_ref, jurisdiction, legal_basis, prize_description, estimated_value, disposition, payment_terms, share_split, award_trigger FROM __temp__income_prize_details');
        $this->addSql('DROP TABLE __temp__income_prize_details');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_PRIZE_INCOME ON income_prize_details (income_id)');

        // income_services_details: drop vessel_id
        $this->addSql('CREATE TEMPORARY TABLE __temp__income_services_details AS SELECT id, income_id, location, service_type, requested_by, start_day, start_year, end_day, end_year, work_summary, parts_materials, risks, payment_terms, extras, liability_limit, cancellation_terms FROM income_services_details');
        $this->addSql('DROP TABLE income_services_details');
        $this->addSql('CREATE TABLE income_services_details (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, income_id INTEGER NOT NULL, location VARCHAR(255) DEFAULT NULL, service_type VARCHAR(255) DEFAULT NULL, requested_by VARCHAR(255) DEFAULT NULL, start_day INTEGER DEFAULT NULL, start_year INTEGER DEFAULT NULL, end_day INTEGER DEFAULT NULL, end_year INTEGER DEFAULT NULL, work_summary CLOB DEFAULT NULL, parts_materials CLOB DEFAULT NULL, risks CLOB DEFAULT NULL, payment_terms CLOB DEFAULT NULL, extras CLOB DEFAULT NULL, liability_limit NUMERIC(11, 2) DEFAULT NULL, cancellation_terms CLOB DEFAULT NULL, CONSTRAINT FK_SERVICES_INCOME FOREIGN KEY (income_id) REFERENCES income (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO income_services_details (id, income_id, location, service_type, requested_by, start_day, start_year, end_day, end_year, work_summary, parts_materials, risks, payment_terms, extras, liability_limit, cancellation_terms) SELECT id, income_id, location, service_type, requested_by, start_day, start_year, end_day, end_year, work_summary, parts_materials, risks, payment_terms, extras, liability_limit, cancellation_terms FROM __temp__income_services_details');
        $this->addSql('DROP TABLE __temp__income_services_details');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SERVICES_INCOME ON income_services_details (income_id)');

        // income_insurance_details: drop claim_id
        $this->addSql('CREATE TEMPORARY TABLE __temp__income_insurance_details AS SELECT id, income_id, policy_number, incident_ref, incident_day, incident_year, incident_location, incident_cause, loss_type, verified_loss, deductible, payment_terms, acceptance_effect, subrogation_terms, coverage_notes FROM income_insurance_details');
        $this->addSql('DROP TABLE income_insurance_details');
        $this->addSql('CREATE TABLE income_insurance_details (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, income_id INTEGER NOT NULL, policy_number VARCHAR(100) DEFAULT NULL, incident_ref VARCHAR(100) DEFAULT NULL, incident_day INTEGER DEFAULT NULL, incident_year INTEGER DEFAULT NULL, incident_location VARCHAR(255) DEFAULT NULL, incident_cause VARCHAR(255) DEFAULT NULL, loss_type VARCHAR(255) DEFAULT NULL, verified_loss NUMERIC(11, 2) DEFAULT NULL, deductible NUMERIC(11, 2) DEFAULT NULL, payment_terms CLOB DEFAULT NULL, acceptance_effect CLOB DEFAULT NULL, subrogation_terms CLOB DEFAULT NULL, coverage_notes CLOB DEFAULT NULL, CONSTRAINT FK_INSURANCE_INCOME FOREIGN KEY (income_id) REFERENCES income (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO income_insurance_details (id, income_id, policy_number, incident_ref, incident_day, incident_year, incident_location, incident_cause, loss_type, verified_loss, deductible, payment_terms, acceptance_effect, subrogation_terms, coverage_notes) SELECT id, income_id, policy_number, incident_ref, incident_day, incident_year, incident_location, incident_cause, loss_type, verified_loss, deductible, payment_terms, acceptance_effect, subrogation_terms, coverage_notes FROM __temp__income_insurance_details');
        $this->addSql('DROP TABLE __temp__income_insurance_details');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INSURANCE_INCOME ON income_insurance_details (income_id)');

        // income_interest_details: drop receipt_id
        $this->addSql('CREATE TEMPORARY TABLE __temp__income_interest_details AS SELECT id, income_id, account_ref, instrument, principal, interest_rate, start_day, start_year, end_day, end_year, calc_method, interest_earned, net_paid, payment_terms, dispute_window FROM income_interest_details');
        $this->addSql('DROP TABLE income_interest_details');
        $this->addSql('CREATE TABLE income_interest_details (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, income_id INTEGER NOT NULL, account_ref VARCHAR(100) DEFAULT NULL, instrument VARCHAR(255) DEFAULT NULL, principal NUMERIC(11, 2) DEFAULT NULL, interest_rate NUMERIC(11, 2) DEFAULT NULL, start_day INTEGER DEFAULT NULL, start_year INTEGER DEFAULT NULL, end_day INTEGER DEFAULT NULL, end_year INTEGER DEFAULT NULL, calc_method VARCHAR(100) DEFAULT NULL, interest_earned NUMERIC(11, 2) DEFAULT NULL, net_paid NUMERIC(11, 2) DEFAULT NULL, payment_terms CLOB DEFAULT NULL, dispute_window CLOB DEFAULT NULL, CONSTRAINT FK_INTEREST_INCOME FOREIGN KEY (income_id) REFERENCES income (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO income_interest_details (id, income_id, account_ref, instrument, principal, interest_rate, start_day, start_year, end_day, end_year, calc_method, interest_earned, net_paid, payment_terms, dispute_window) SELECT id, income_id, account_ref, instrument, principal, interest_rate, start_day, start_year, end_day, end_year, calc_method, interest_earned, net_paid, payment_terms, dispute_window FROM __temp__income_interest_details');
        $this->addSql('DROP TABLE __temp__income_interest_details');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INTEREST_INCOME ON income_interest_details (income_id)');
    }

    public function down(Schema $schema): void
    {
        // Revert non supportato su SQLite senza ricreare le colonne: ripristinare manualmente se necessario.
    }
}
