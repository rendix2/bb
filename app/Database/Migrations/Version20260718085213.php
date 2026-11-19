<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20260718085213 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create group_member table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.group_member');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('group_id', Types::BIGINT)
            ->setComment('Group ID');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        // the primary key (user_id, group_id) makes a membership unique
        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(
            new UnqualifiedName(Identifier::unquoted('user_id')),
            new UnqualifiedName(Identifier::unquoted('group_id'))
        );

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Group members')

            ->addIndex(['user_id'], 'K__Group_member__User_id')
            ->addIndex(['group_id'], 'K__Group_member__Group_id')

            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Group_member__User_id')
            ->addForeignKeyConstraint('group', ['group_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Group_member__Group_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.group_member');
    }

}
