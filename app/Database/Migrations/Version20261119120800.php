<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20261119120800 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create topic_move table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.topic_move');

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

        $table->addColumn('topic_id', Types::BIGINT)
            ->setComment('Topic ID');

        $table->addColumn('from_forum_id', Types::BIGINT)
            ->setComment('Forum the topic was moved from');

        $table->addColumn('to_forum_id', Types::BIGINT)
            ->setComment('Forum the topic was moved to');

        $table->addColumn('user_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Moved by (user ID)');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Moves of topics to another forum')

            ->addIndex(['from_forum_id'], 'K__Topic_move__From_forum_id')
            ->addIndex(['to_forum_id'], 'K__Topic_move__To_forum_id')
            ->addIndex(['topic_id'], 'K__Topic_move__Topic_id')
            ->addIndex(['user_id'], 'K__Topic_move__User_id')
            ->addUniqueIndex(['uuid'], 'UK__Topic_move__Uuid')

            ->addForeignKeyConstraint('forum', ['from_forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Topic_move__From_forum_id')
            ->addForeignKeyConstraint('forum', ['to_forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Topic_move__To_forum_id')
            ->addForeignKeyConstraint('topic', ['topic_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Topic_move__Topic_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'SET NULL'], name: 'FK__Topic_move__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.topic_move');
    }

}
