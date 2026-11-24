<?php declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Types\IpAddressType;
use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260718093503 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create post table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.post');

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

        $table->addColumn('add_ip_address', IpAddressType::NAME)
            ->setComment('Add IP address');

        $table->addColumn('edit_ip_address', IpAddressType::NAME)
            ->setNotnull(false)
            ->setComment('Edit IP address');

        $table->addColumn('is_locked', Types::BOOLEAN)
            ->setComment('Is locked?');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('topic_id', Types::BIGINT)
            ->setComment('Topic ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Posts')

            ->addIndex(['topic_id'], 'K__Post__Topic_id')
            ->addIndex(['user_id'], 'K__Post__User_id')
            ->addUniqueIndex(['uuid'], 'UK__Post__Uuid')

            ->addForeignKeyConstraint('topic', ['topic_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post__Topic_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Post__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.post');
    }

    public function postUp(Schema $schema) : void
    {
        // the full-text search (Forum:Search): a GIN expression index, DBAL cannot express it; the expression must stay the same as in App\Database\Query\TsMatchFunction (simple, COALESCE(title) || text)
        $this->connection->executeStatement("CREATE INDEX K__Post__Search ON public.post USING GIN (to_tsvector('simple', COALESCE(title, '') || ' ' || text))");
    }

}
