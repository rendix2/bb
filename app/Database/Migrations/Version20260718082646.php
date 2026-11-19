<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260718082646 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create moderator_user table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.moderator_user');

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

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('forum_id', Types::BIGINT)
            ->setComment('Forum ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Users moderating forums')

            ->addIndex(['forum_id'], 'K__Moderator_user__Forum_id')
            ->addIndex(['user_id'], 'K__Moderator_user__User_id')
            ->addUniqueIndex(['uuid'], 'UK__Moderator_user__Uuid')

            ->addForeignKeyConstraint('forum', ['forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Moderator_user__Forum_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Moderator_user__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.moderator_user');
    }

}
