<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20230621145917 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create language table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.language');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('name', Types::STRING)
            ->setLength(512)
            ->setComment('Name');

        $table->addColumn('file_name', Types::STRING)
            ->setLength(512)
            ->setComment('File name');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Languages')

            ->addUniqueIndex(['uuid'], 'UK__Language__Uuid');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.language');
    }

}
