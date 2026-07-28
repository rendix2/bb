<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260728221123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('public.users');

        $table
            ->addIndex(['language_id'], 'K__User__Language_id')

            ->addForeignKeyConstraint('language', ['language_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__User__Language_id');
    }

    public function down(Schema $schema): void
    {
    }
}
