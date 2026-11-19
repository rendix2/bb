<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20260720123217 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create private_message_participant table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.private_message_participant');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('is_read', Types::BOOLEAN)
            ->setComment('Is read');

        $table->addColumn('is_deleted', Types::BOOLEAN)
            ->setComment('Is deleted');

        $table->addColumn('is_starred', Types::BOOLEAN)
            ->setComment('Is starred');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('thread_id', Types::BIGINT)
            ->setComment('Private message Thread ID');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Private message participants')

            ->addIndex(['thread_id'], 'K__Private_message_participant__Thread_id')
            ->addIndex(['user_id'], 'K__Private_message_participant__User_id')
            ->addUniqueIndex(['user_id', 'thread_id'], 'UK__Private_message_participant__User_id_thread_id')

            ->addForeignKeyConstraint('private_message_thread', ['thread_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Private_message_participant__Thread_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Private_message_participant__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.private_message_participant');
    }

}
