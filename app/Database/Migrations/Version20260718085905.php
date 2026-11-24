<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260718085905 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create topic table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.topic');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('name', Types::STRING)
            ->setLength(1024)
            ->setComment('Name');

        $table->addColumn('is_locked', Types::BOOLEAN)
            ->setComment('Is locked?');

        $table->addColumn('view_count', Types::INTEGER)
            ->setComment('View count');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('category_id', Types::BIGINT)
            ->setComment('Category ID');

        $table->addColumn('forum_id', Types::BIGINT)
            ->setComment('Forum ID');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Topics')

            ->addIndex(['category_id'], 'K__Topic__Category_id')
            ->addIndex(['forum_id'], 'K__Topic__Forum_id')
            ->addIndex(['user_id'], 'K__Topic__User_id')
            ->addUniqueIndex(['uuid'], 'UK__Topic__Uuid')

            ->addForeignKeyConstraint('category', ['category_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Topic__Category_id')
            ->addForeignKeyConstraint('forum', ['forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Topic__Forum_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Topic__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.topic');
    }

    public function postUp(Schema $schema) : void
    {
        // the full-text search (Forum:Search): a GIN expression index, DBAL cannot express it; the expression must stay the same as in App\Database\Query\TsMatchFunction (simple, name)
        $this->connection->executeStatement("CREATE INDEX K__Topic__Search ON public.topic USING GIN (to_tsvector('simple', name))");
    }

}
