<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create local_law table with code, description, disclaimer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE local_law (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description VARCHAR(255) NOT NULL, disclaimer VARCHAR(255) DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE local_law');
    }
}
