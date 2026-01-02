<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rende nullable signingDay e signingYear su income (compatibile con SQLite)';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        if ($platform === 'sqlite') {
            // Ricrea la tabella income rendendo nullable signing_day e signing_year
            $this->addSql('PRAGMA foreign_keys = OFF');
            $this->addSql('CREATE TABLE income_tmp (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, income_category_id INTEGER NOT NULL, ship_id INTEGER NOT NULL, user_id INTEGER DEFAULT NULL, code VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, signing_day INTEGER DEFAULT NULL, signing_year INTEGER DEFAULT NULL, payment_day INTEGER DEFAULT NULL, payment_year INTEGER DEFAULT NULL, amount NUMERIC(11, 2) NOT NULL, note CLOB DEFAULT NULL, cancel_day INTEGER DEFAULT NULL, cancel_year INTEGER DEFAULT NULL, expiration_day INT DEFAULT NULL, expiration_year INT DEFAULT NULL, company_id INTEGER DEFAULT NULL, local_law_id INTEGER DEFAULT NULL, CONSTRAINT FK_3FA862D053F8702F FOREIGN KEY (income_category_id) REFERENCES income_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3FA862D0C256317D FOREIGN KEY (ship_id) REFERENCES ship (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3FA862D0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('INSERT INTO income_tmp (id, income_category_id, ship_id, user_id, code, title, signing_day, signing_year, payment_day, payment_year, amount, note, cancel_day, cancel_year, expiration_day, expiration_year, company_id, local_law_id) SELECT id, income_category_id, ship_id, user_id, code, title, signing_day, signing_year, payment_day, payment_year, amount, note, cancel_day, cancel_year, expiration_day, expiration_year, company_id, local_law_id FROM income');
            $this->addSql('DROP TABLE income');
            $this->addSql('ALTER TABLE income_tmp RENAME TO income');
            $this->addSql('CREATE INDEX IDX_3FA862D053F8702F ON income (income_category_id)');
            $this->addSql('CREATE INDEX IDX_3FA862D0C256317D ON income (ship_id)');
            $this->addSql('CREATE INDEX IDX_3FA862D0A76ED395 ON income (user_id)');
            $this->addSql('CREATE INDEX IDX_249AA25C979B1AD6 ON income (company_id)');
            $this->addSql('CREATE INDEX IDX_INCOME_LOCAL_LAW_ID ON income (local_law_id)');
            $this->addSql('PRAGMA foreign_keys = ON');

            return;
        }

        $this->addSql('ALTER TABLE income ALTER COLUMN signing_day DROP NOT NULL');
        $this->addSql('ALTER TABLE income ALTER COLUMN signing_year DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Non si ripristina il NOT NULL per evitare inconsistenze su dati già null.
    }
}
