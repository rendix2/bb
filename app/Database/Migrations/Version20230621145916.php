<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20230621145916 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create rank table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.rank');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('name', Types::STRING)
            ->setLength(512)
            ->setComment('Name');

        $table->addColumn('min_posts', Types::INTEGER)
            ->setNotnull(false)
            ->setComment('Minimum posts');

        $table->addColumn('file_name', Types::TEXT)
            ->setComment('File name');

        $table->addColumn('is_special', Types::BOOLEAN)
            ->setComment('Is special');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Ranks')

            ->addUniqueIndex(['file_name'], 'UK__Rank__File_name')
            ->addUniqueIndex(['uuid'], 'UK__Rank__Uuid');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.rank');
    }

    public function postUp(Schema $schema) : void
    {
        // a CHECK constraint, DBAL cannot express it: the minimum count of posts is never negative
        $this->connection->executeStatement("ALTER TABLE public.rank ADD CONSTRAINT CH__Rank__Min_posts CHECK (min_posts >= 0)");
    }

}
