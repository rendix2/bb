<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260718085638 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create user_forum table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.user_forum');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('topic_add', Types::BOOLEAN)
            ->setComment('May start a topic');

        $table->addColumn('topic_update', Types::BOOLEAN)
            ->setComment('May edit his topic');

        $table->addColumn('topic_delete', Types::BOOLEAN)
            ->setComment('May delete his topic');

        $table->addColumn('post_add', Types::BOOLEAN)
            ->setComment('May reply');

        $table->addColumn('post_update', Types::BOOLEAN)
            ->setComment('May edit his post');

        $table->addColumn('post_delete', Types::BOOLEAN)
            ->setComment('May delete his post');

        $table->addColumn('topic_thank', Types::BOOLEAN)
            ->setComment('May thank');

        $table->addColumn('topic_fast_reply', Types::BOOLEAN)
            ->setComment('May use the fast reply');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('forum_id', Types::BIGINT)
            ->setComment('Forum ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Forum access of users')

            ->addIndex(['forum_id'], 'K__User_forum__Forum_id')
            ->addIndex(['user_id'], 'K__User_forum__User_id')
            ->addUniqueIndex(['user_id', 'forum_id'], 'UK__User_forum__User_id_forum_id')
            ->addUniqueIndex(['uuid'], 'UK__User_forum__Uuid')

            ->addForeignKeyConstraint('forum', ['forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__User_forum__Forum_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__User_forum__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.user_forum');
    }

}
