<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260720123214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('public.private_message');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('thread_id', Types::BIGINT)
            ->setComment('Private message Thread ID');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('Author User ID');

        $table->addColumn('text', Types::TEXT)
            ->setComment('Text');

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
            ->setComment('Private messages')
            ->addIndex(['user_id'], 'K__Private_message__User_id')
            ->addIndex(['thread_id'], 'K__Private_message__Thread_id')

            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Private_message__User_id')
            ->addForeignKeyConstraint('private_message_thread', ['thread_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Private_message__Thread_id');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('private_message');
    }
}
