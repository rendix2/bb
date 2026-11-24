<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260717233326 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create groups table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.groups');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('name', Types::STRING)
            ->setLength(512)
            ->setComment('Name');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('moderator_id', Types::BIGINT)
            ->setComment('Moderator ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Groups')

            ->addIndex(['moderator_id'], 'K__Groups__Moderator_id')
            ->addUniqueIndex(['uuid'], 'UK__Groups__Uuid')

            ->addForeignKeyConstraint('users', ['moderator_id'], ['id'], name: 'FK__Groups__Moderator_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.groups');
    }

}
