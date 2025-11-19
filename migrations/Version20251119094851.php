<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119094851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crew (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ship_id INTEGER DEFAULT NULL, name VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, nickname VARCHAR(100) DEFAULT NULL, birth_year INTEGER DEFAULT NULL, birth_day INTEGER DEFAULT NULL, birth_world VARCHAR(100) DEFAULT NULL, code VARCHAR(36) NOT NULL, CONSTRAINT FK_894940B2C256317D FOREIGN KEY (ship_id) REFERENCES ship (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_894940B2C256317D ON crew (ship_id)');
        $this->addSql('CREATE TABLE crew_ship_role (crew_id INTEGER NOT NULL, ship_role_id INTEGER NOT NULL, PRIMARY KEY(crew_id, ship_role_id), CONSTRAINT FK_C71AD6D25FE259F6 FOREIGN KEY (crew_id) REFERENCES crew (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C71AD6D2D82E12C1 FOREIGN KEY (ship_role_id) REFERENCES ship_role (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C71AD6D25FE259F6 ON crew_ship_role (crew_id)');
        $this->addSql('CREATE INDEX IDX_C71AD6D2D82E12C1 ON crew_ship_role (ship_role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE crew');
        $this->addSql('DROP TABLE crew_ship_role');
    }
}
