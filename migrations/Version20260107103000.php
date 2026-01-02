<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rende nullable signingDay e signingYear su income';
    }

    public function up(Schema $schema): void
    {
        try {
            $this->addSql('ALTER TABLE income ALTER COLUMN signing_day DROP NOT NULL');
        } catch (\Throwable $e) {
            // SQLite fallback
            try {
                $this->addSql('ALTER TABLE income ALTER COLUMN signing_day SET DEFAULT NULL');
            } catch (\Throwable $ignored) {
            }
        }

        try {
            $this->addSql('ALTER TABLE income ALTER COLUMN signing_year DROP NOT NULL');
        } catch (\Throwable $e) {
            // SQLite fallback
            try {
                $this->addSql('ALTER TABLE income ALTER COLUMN signing_year SET DEFAULT NULL');
            } catch (\Throwable $ignored) {
            }
        }
    }

    public function down(Schema $schema): void
    {
        // Down migration not enforced because existing NULLs would break NOT NULL constraint.
    }
}
