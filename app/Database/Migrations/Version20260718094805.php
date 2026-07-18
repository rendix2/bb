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
final class Version20260718094805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('public.report');

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

        $table->addColumn('post_id', Types::BIGINT)
            ->setComment('Post ID');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('ip_address', IpAddressType::NAME)
            ->setComment('IP Address');

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
            ->setComment('Post Reports')
            ->addIndex(['user_id'], 'K__Report__User_id')
            ->addIndex(['category_id'], 'K__Report__Category_id')
            ->addIndex(['forum_id'], 'K__Report__Forum_id')
            ->addIndex(['topic_id'], 'K__Report__Topic_id')
            ->addIndex(['post_id'], 'K__Report__Post_id')

            ->addForeignKeyConstraint('users', ['user_id'], ['id'], name: 'FK__Report__User_id')
            ->addForeignKeyConstraint('category', ['category_id'], ['id'], name: 'FK__Report__Category_id')
            ->addForeignKeyConstraint('forum', ['forum_id'], ['id'], name: 'FK__Report__Forum_id')
            ->addForeignKeyConstraint('topic', ['topic_id'], ['id'], name: 'FK__Report__Topic_id')
            ->addForeignKeyConstraint('post', ['post_id'], ['id'], name: 'FK__Report__Post_id');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
