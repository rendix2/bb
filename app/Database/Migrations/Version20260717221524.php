<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260717221524 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create forum table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.forum');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('name', Types::TEXT)
            ->setComment('Name');

        $table->addColumn('description', Types::TEXT)
            ->setNotnull(false)
            ->setComment('Description');

        $table->addColumn('active', Types::BOOLEAN)
            ->setComment('Active');

        $table->addColumn('rules', Types::TEXT)
            ->setNotnull(false)
            ->setComment('Rules');

        $table->addColumn('sort_order', Types::INTEGER)
            ->setComment('Order');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('category_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Category ID');

        $table->addColumn('parent_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Parent ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Forums')

            ->addIndex(['category_id'], 'K__Forum__Category_id')
            ->addIndex(['parent_id'], 'K__Forum__Parent_id')
            ->addUniqueIndex(['uuid'], 'UK__Forum__Uuid')

            ->addForeignKeyConstraint('category', ['category_id'], ['id'], name: 'FK__Forum__Category_id')
            ->addForeignKeyConstraint('forum', ['parent_id'], ['id'], name: 'FK__Forum__Parent_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.forum');
    }

}
