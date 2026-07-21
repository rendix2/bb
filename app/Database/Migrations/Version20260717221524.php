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
final class Version20260717221524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('public.forum');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('parent_id', Types::BIGINT)
            ->setComment('Parent ID');

        $table->addColumn('category_id', Types::BIGINT)
            ->setComment('Category ID');

        $table->addColumn('name', Types::STRING)
            ->setComment('Name')
            ->setLength(512);

        $table->addColumn('description', Types::TEXT)
            ->setComment('Description');

        $table->addColumn('active', Types::BOOLEAN)
            ->setComment('Active');

        $table->addColumn('order', Types::INTEGER)
            ->setComment('Order');

        $table->addColumn('thank', Types::INTEGER)
            ->setComment('Thank');

        $table->addColumn('topic_count', Types::INTEGER)
            ->setComment('Topic count');

        $table->addColumn('post_count', Types::INTEGER)
            ->setComment('Post count');

        $table->addColumn('post_add', Types::BOOLEAN)
            ->setComment('Can add post');

        $table->addColumn('post_delete', Types::BOOLEAN)
            ->setComment('Can delete post');

        $table->addColumn('post_update', Types::BOOLEAN)
            ->setComment('Can update post');

        $table->addColumn('topic_add', Types::BOOLEAN)
            ->setComment('Can add topic');

        $table->addColumn('topic_delete', Types::BOOLEAN)
            ->setComment('Can delete topic');

        $table->addColumn('topic_update', Types::BOOLEAN)
            ->setComment('Can update topic');

        $table->addColumn('fast_reply', Types::BOOLEAN)
            ->setComment('Can add fast reply (post)');

        $table->addColumn('rules', Types::TEXT)
            ->setComment('Rules');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create());

        $table
            ->setComment('Forums')
            ->addIndex(['parent_id'], 'K__Forum__Parent_id')
            ->addIndex(['category_id'], 'K__Forum__Category_id')

            ->addForeignKeyConstraint('forum', ['parent_id'], ['id'], name: 'FK__Forum__Parent_id')
            ->addForeignKeyConstraint('category', ['category_id'], ['id'], name: 'FK__Category__id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
