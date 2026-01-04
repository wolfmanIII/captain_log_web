<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260111100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Aggiunge tabella campaign e relazione con ship';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        if ($platform === 'sqlite') {
            $this->addSql('CREATE TABLE campaign (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, code BLOB NOT NULL, title VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, starting_year INTEGER DEFAULT NULL, session_day INTEGER DEFAULT NULL, session_year INTEGER DEFAULT NULL)');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_F639F77477153098 ON campaign (code)');
            $this->addSql('ALTER TABLE ship ADD COLUMN campaign_id INTEGER DEFAULT NULL');
            $this->addSql('CREATE INDEX IDX_EF4E8F97F639F774 ON ship (campaign_id)');
            // SQLite non supporta ALTER TABLE ... ADD CONSTRAINT; FK non applicata qui.
        } else {
            $this->addSql('CREATE TABLE campaign (id INT AUTO_INCREMENT NOT NULL, code UUID NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, starting_year INT DEFAULT NULL, session_day INT DEFAULT NULL, session_year INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_F639F77477153098 ON campaign (code)');
            $this->addSql('ALTER TABLE ship ADD campaign_id INT DEFAULT NULL');
            $this->addSql('ALTER TABLE ship ADD CONSTRAINT FK_EF4E8F97F639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
            $this->addSql('CREATE INDEX IDX_EF4E8F97F639F774 ON ship (campaign_id)');
        }
    }

    public function down(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        if ($platform === 'sqlite') {
            $this->addSql('DROP TABLE campaign');
            $this->addSql('DROP INDEX IDX_EF4E8F97F639F774 ON ship');
            // Nota: rimozione della colonna campaign_id in SQLite richiederebbe rebuild della tabella; omessa nel down.
        } else {
            $this->addSql('ALTER TABLE ship DROP FOREIGN KEY FK_EF4E8F97F639F774');
            $this->addSql('DROP TABLE campaign');
            $this->addSql('DROP INDEX IDX_EF4E8F97F639F774 ON ship');
            $this->addSql('ALTER TABLE ship DROP campaign_id');
        }
    }
}
