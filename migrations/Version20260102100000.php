<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260102100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea la tabella income_category';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('income_category');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('code', 'string', ['length' => 10]);
        $table->addColumn('description', 'string', ['length' => 255]);

        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('income_category');
    }
}
