<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Types\IpAddressType;
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
final class Version20260718094205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('public.post');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('category_id', Types::BIGINT)
            ->setComment('Category ID');

        $table->addColumn('forum_id', Types::BIGINT)
            ->setComment('Forum ID');

        $table->addColumn('topic_id', Types::BIGINT)
            ->setComment('Topic ID');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('title', Types::STRING)
            ->setLength(512)
            ->setComment('Title');

        $table->addColumn('text', Types::TEXT)
            ->setComment('Text');

        $table->addColumn('add_ip_address', IpAddressType::NAME)
            ->setComment('Add IP Address');

        $table->addColumn('edit_ip_address', IpAddressType::NAME)
            ->setComment('Add IP Address');

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
            ->setComment('Posts')
            ->addIndex(['user_id'], 'K__Post__User_id')
            ->addIndex(['category_id'], 'K__Post__Category_id')
            ->addIndex(['forum_id'], 'K__Post__Forum_id')
            ->addIndex(['topic_id'], 'K__Post__Topic_id')

            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post__User_id')
            ->addForeignKeyConstraint('category', ['category_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post__Category_id')
            ->addForeignKeyConstraint('forum', ['forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post__Forum_id')
            ->addForeignKeyConstraint('topic', ['topic_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post__Topic_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
