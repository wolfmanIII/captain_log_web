<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260106233000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aggiunge loss_refund su insurance';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE insurance ADD COLUMN loss_refund NUMERIC(5, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__insurance AS SELECT id, name, annual_cost, coverage FROM insurance');
        $this->addSql('DROP TABLE insurance');
        $this->addSql('CREATE TABLE insurance (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, annual_cost NUMERIC(11, 2) NOT NULL, coverage CLOB DEFAULT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO insurance (id, name, annual_cost, coverage) SELECT id, name, annual_cost, coverage FROM __temp__insurance');
        $this->addSql('DROP TABLE __temp__insurance');
    }
}
