<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260106240000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aggiunge user_id a campaign per ownership';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE campaign ADD COLUMN user_id INTEGER DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TEMPORARY TABLE __temp__campaign AS SELECT id, title, code, description, starting_year, session_day, session_year FROM campaign');
        $this->addSql('DROP TABLE campaign');
        $this->addSql('CREATE TABLE campaign (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, code VARCHAR(36) NOT NULL, description CLOB DEFAULT NULL, starting_year INTEGER DEFAULT NULL, session_day INTEGER DEFAULT NULL, session_year INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO campaign (id, title, code, description, starting_year, session_day, session_year) SELECT id, title, code, description, starting_year, session_day, session_year FROM __temp__campaign');
        $this->addSql('DROP TABLE __temp__campaign');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F639F77477153098 ON campaign (code)');
    }
}
