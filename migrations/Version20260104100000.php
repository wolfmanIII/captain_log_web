<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260104100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea tabella di contesto company_role con code/description';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE company_role (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code VARCHAR(10) NOT NULL, description VARCHAR(255) NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE company_role');
    }
}
