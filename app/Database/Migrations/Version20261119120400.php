<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20261119120400 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create poll_vote table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.poll_vote');

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

        $table->addColumn('category_id', Types::BIGINT)
            ->setComment('Category ID');

        $table->addColumn('forum_id', Types::BIGINT)
            ->setComment('Forum ID');

        $table->addColumn('topic_id', Types::BIGINT)
            ->setComment('Topic ID');

        $table->addColumn('poll_id', Types::BIGINT)
            ->setComment('Poll ID');

        $table->addColumn('poll_answer_id', Types::BIGINT)
            ->setComment('Poll answer ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Poll vote')

            ->addIndex(['category_id'], 'K__Poll_vote__Category_id')
            ->addIndex(['forum_id'], 'K__Poll_vote__Forum_id')
            ->addIndex(['poll_answer_id'], 'K__Poll_vote__Poll_answer_id')
            ->addIndex(['poll_id'], 'K__Poll_vote__Poll_id')
            ->addIndex(['topic_id'], 'K__Poll_vote__Topic_id')
            ->addIndex(['user_id'], 'K__Poll_vote__User_id')
            ->addUniqueIndex(['poll_id', 'user_id'], 'UK__Poll_vote__Poll_id_user_id')
            ->addUniqueIndex(['uuid'], 'UK__Poll_vote__Uuid')

            ->addForeignKeyConstraint('category', ['category_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Poll_vote__Category_id')
            ->addForeignKeyConstraint('forum', ['forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Poll_vote__Forum_id')
            ->addForeignKeyConstraint('poll', ['poll_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Poll_vote__Poll_id')
            ->addForeignKeyConstraint('poll_answer', ['poll_answer_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Poll_vote__Poll_answer_id')
            ->addForeignKeyConstraint('topic', ['topic_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Poll_vote__Topic_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Poll_vote__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.poll_vote');
    }

}
