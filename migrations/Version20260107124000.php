<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107124000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea le tabelle income_mail_details e income_interest_details (FK 1:1 con income)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE income_mail_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    income_id INTEGER NOT NULL,
    origin VARCHAR(255) DEFAULT NULL,
    destination VARCHAR(255) DEFAULT NULL,
    dispatch_day INTEGER DEFAULT NULL,
    dispatch_year INTEGER DEFAULT NULL,
    delivery_day INTEGER DEFAULT NULL,
    delivery_year INTEGER DEFAULT NULL,
    mail_type VARCHAR(255) DEFAULT NULL,
    package_count INTEGER DEFAULT NULL,
    total_mass NUMERIC(11, 2) DEFAULT NULL,
    security_level VARCHAR(255) DEFAULT NULL,
    seal_codes VARCHAR(255) DEFAULT NULL,
    payment_terms CLOB DEFAULT NULL,
    proof_of_delivery CLOB DEFAULT NULL,
    liability_limit NUMERIC(11, 2) DEFAULT NULL,
    CONSTRAINT FK_MAIL_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_MAIL_DETAILS_INCOME ON income_mail_details (income_id)');

        $this->addSql(<<<'SQL'
CREATE TABLE income_interest_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    income_id INTEGER NOT NULL,
    receipt_id VARCHAR(100) DEFAULT NULL,
    account_ref VARCHAR(100) DEFAULT NULL,
    instrument VARCHAR(255) DEFAULT NULL,
    principal NUMERIC(11, 2) DEFAULT NULL,
    interest_rate NUMERIC(11, 2) DEFAULT NULL,
    start_day INTEGER DEFAULT NULL,
    start_year INTEGER DEFAULT NULL,
    end_day INTEGER DEFAULT NULL,
    end_year INTEGER DEFAULT NULL,
    calc_method VARCHAR(100) DEFAULT NULL,
    interest_earned NUMERIC(11, 2) DEFAULT NULL,
    net_paid NUMERIC(11, 2) DEFAULT NULL,
    payment_terms CLOB DEFAULT NULL,
    dispute_window CLOB DEFAULT NULL,
    CONSTRAINT FK_INTEREST_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_INTEREST_DETAILS_INCOME ON income_interest_details (income_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS income_mail_details');
        $this->addSql('DROP TABLE IF EXISTS income_interest_details');
    }
}
