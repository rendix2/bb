<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260718094030 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create topic_watch table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.topic_watch');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('topic_id', Types::BIGINT)
            ->setComment('Topic ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('user_id')), new UnqualifiedName(Identifier::unquoted('topic_id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Topic watches')

            ->addIndex(['topic_id'], 'K__Topic_watch__Topic_id')
            ->addIndex(['user_id'], 'K__Topic_watch__User_id')
            ->addUniqueIndex(['uuid'], 'UK__Topic_watch__Uuid')

            ->addForeignKeyConstraint('topic', ['topic_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Topic_watch__Topic_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Topic_watch__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.topic_watch');
    }

}
