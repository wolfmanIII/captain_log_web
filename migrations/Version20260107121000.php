<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rimuove start/end day/year e total da income_services_details (ridondanti con income)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE income_services_details_tmp (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, income_id INTEGER NOT NULL, location VARCHAR(255) DEFAULT NULL, vessel_id VARCHAR(255) DEFAULT NULL, service_type VARCHAR(255) DEFAULT NULL, requested_by VARCHAR(255) DEFAULT NULL, work_summary CLOB DEFAULT NULL, parts_materials CLOB DEFAULT NULL, risks CLOB DEFAULT NULL, payment_terms CLOB DEFAULT NULL, extras CLOB DEFAULT NULL, liability_limit NUMERIC(11, 2) DEFAULT NULL, cancellation_terms CLOB DEFAULT NULL, CONSTRAINT FK_SERVICES_INCOME FOREIGN KEY (income_id) REFERENCES income (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO income_services_details_tmp (id, income_id, location, vessel_id, service_type, requested_by, work_summary, parts_materials, risks, payment_terms, extras, liability_limit, cancellation_terms) SELECT id, income_id, location, vessel_id, service_type, requested_by, work_summary, parts_materials, risks, payment_terms, extras, liability_limit, cancellation_terms FROM income_services_details');
        $this->addSql('DROP TABLE income_services_details');
        $this->addSql('ALTER TABLE income_services_details_tmp RENAME TO income_services_details');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_INCOME_SERVICES_DETAILS_INCOME ON income_services_details (income_id)');
    }

    public function down(Schema $schema): void
    {
        // Non si ripristinano le colonne rimosse per evitare perdita dati incoerenti.
    }
}
