<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260730221304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('public.topic');

        $table
            ->addIndex(['first_post_id'], 'K__Topic__First_post_id')
            ->addIndex(['last_post_id'], 'K__Topic__Last_post_id')
            ->addIndex(['poll_id'], 'K__Topic__Poll_id')

            ->addForeignKeyConstraint('post', ['first_post_id'], ['id'], name: 'FK__Topic__First_post_id')
            ->addForeignKeyConstraint('post', ['last_post_id'], ['id'], name: 'FK__Topic__Last_post_id')
            ->addForeignKeyConstraint('category', ['category_id'], ['id'], name: 'FK__Topic__Category_id');



    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
