<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107115500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rimuove fare_total da income_passengers_details (ridondante con income.amount)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE income_passengers_details_tmp (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, income_id INTEGER NOT NULL, origin VARCHAR(255) DEFAULT NULL, destination VARCHAR(255) DEFAULT NULL, departure_day INTEGER DEFAULT NULL, departure_year INTEGER DEFAULT NULL, arrival_day INTEGER DEFAULT NULL, arrival_year INTEGER DEFAULT NULL, class_or_berth VARCHAR(100) DEFAULT NULL, qty INTEGER DEFAULT NULL, passenger_names CLOB DEFAULT NULL, passenger_contact VARCHAR(255) DEFAULT NULL, baggage_allowance VARCHAR(255) DEFAULT NULL, extra_baggage VARCHAR(255) DEFAULT NULL, payment_terms CLOB DEFAULT NULL, refund_change_policy CLOB DEFAULT NULL, CONSTRAINT FK_PASSENGERS_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO income_passengers_details_tmp (id, income_id, origin, destination, departure_day, departure_year, arrival_day, arrival_year, class_or_berth, qty, passenger_names, passenger_contact, baggage_allowance, extra_baggage, payment_terms, refund_change_policy) SELECT id, income_id, origin, destination, departure_day, departure_year, arrival_day, arrival_year, class_or_berth, qty, passenger_names, passenger_contact, baggage_allowance, extra_baggage, payment_terms, refund_change_policy FROM income_passengers_details');
        $this->addSql('DROP TABLE income_passengers_details');
        $this->addSql('ALTER TABLE income_passengers_details_tmp RENAME TO income_passengers_details');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_PASSENGERS_DETAILS_INCOME ON income_passengers_details (income_id)');
    }

    public function down(Schema $schema): void
    {
        // Non si ripristina fare_total per evitare perdita dati incoerenti.
    }
}
