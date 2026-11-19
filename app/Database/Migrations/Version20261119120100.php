<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20261119120100 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create group_forum table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.group_forum');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('group_id', Types::BIGINT)
            ->setComment('Group ID');

        $table->addColumn('forum_id', Types::BIGINT)
            ->setComment('Forum ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Forum access of groups')

            ->addIndex(['forum_id'], 'K__Group_forum__Forum_id')
            ->addIndex(['group_id'], 'K__Group_forum__Group_id')
            ->addUniqueIndex(['uuid'], 'UK__Group_forum__Uuid')

            ->addForeignKeyConstraint('forum', ['forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Group_forum__Forum_id')
            ->addForeignKeyConstraint('groups', ['group_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Group_forum__Group_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.group_forum');
    }

}
