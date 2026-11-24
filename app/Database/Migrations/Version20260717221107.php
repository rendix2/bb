<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260717221107 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create category table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.category');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('name', Types::TEXT)
            ->setComment('Name');

        $table->addColumn('sort_order', Types::INTEGER)
            ->setComment('Order');

        $table->addColumn('active', Types::BOOLEAN)
            ->setComment('Active');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('parent_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Parent ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Categories')

            ->addIndex(['parent_id'], 'K__Category__Parent_id')
            ->addUniqueIndex(['uuid'], 'UK__Category__Uuid')

            ->addForeignKeyConstraint('category', ['parent_id'], ['id'], name: 'FK__Category__Parent_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.category');
    }

}
