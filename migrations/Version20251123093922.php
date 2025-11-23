<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123093922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__insurance AS SELECT id, name, annual_cost, coverage FROM insurance');
        $this->addSql('DROP TABLE insurance');
        $this->addSql('CREATE TABLE insurance (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, annual_cost NUMERIC(11, 2) NOT NULL, coverage CLOB DEFAULT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO insurance (id, name, annual_cost, coverage) SELECT id, name, annual_cost, coverage FROM __temp__insurance');
        $this->addSql('DROP TABLE __temp__insurance');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__insurance AS SELECT id, name, annual_cost, coverage FROM insurance');
        $this->addSql('DROP TABLE insurance');
        $this->addSql('CREATE TABLE insurance (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, annual_cost NUMERIC(11, 2) NOT NULL, coverage CLOB NOT NULL)');
        $this->addSql('INSERT INTO insurance (id, name, annual_cost, coverage) SELECT id, name, annual_cost, coverage FROM __temp__insurance');
        $this->addSql('DROP TABLE __temp__insurance');
    }
}
