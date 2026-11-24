<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260720123211 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create private_message_thread table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.private_message_thread');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('subject', Types::TEXT)
            ->setComment('Subject');

        $table->addColumn('last_message_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Last message at');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('user_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Founder User ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Private message threads')

            ->addIndex(['user_id'], 'K__Private_message_thread__User_id')
            ->addUniqueIndex(['uuid'], 'UK__Private_message_thread__Uuid')

            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'SET NULL'], name: 'FK__Private_message_thread__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.private_message_thread');
    }

}
