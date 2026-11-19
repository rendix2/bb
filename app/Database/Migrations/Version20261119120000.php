<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20261119120000 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create post_history table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.post_history');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('title', Types::TEXT)
            ->setNotnull(false)
            ->setComment('Title');

        $table->addColumn('text', Types::TEXT)
            ->setComment('Text');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('post_id', Types::BIGINT)
            ->setComment('Post ID');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('category_id', Types::BIGINT)
            ->setComment('Category ID');

        $table->addColumn('forum_id', Types::BIGINT)
            ->setComment('Forum ID');

        $table->addColumn('topic_id', Types::BIGINT)
            ->setComment('Topic ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Post history')

            ->addIndex(['category_id'], 'K__Post_history__Category_id')
            ->addIndex(['forum_id'], 'K__Post_history__Forum_id')
            ->addIndex(['post_id'], 'K__Post_history__Post_id')
            ->addIndex(['topic_id'], 'K__Post_history__Topic_id')
            ->addIndex(['user_id'], 'K__Post_history__User_id')
            ->addUniqueIndex(['uuid'], 'UK__Post_history__Uuid')

            ->addForeignKeyConstraint('category', ['category_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post_history__Category_id')
            ->addForeignKeyConstraint('forum', ['forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post_history__Forum_id')
            ->addForeignKeyConstraint('post', ['post_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post_history__Post_id')
            ->addForeignKeyConstraint('topic', ['topic_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post_history__Topic_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post_history__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.post_history');
    }

}
