<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20261119120900 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create ban_reason table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.ban_reason');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('text', Types::TEXT)
            ->setComment('Text of the reason');

        $table->addColumn('sort_order', Types::INTEGER)
            ->setComment('Order');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Predefined reasons of bans')

            ->addUniqueIndex(['uuid'], 'UK__Ban_reason__Uuid');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.ban_reason');
    }

}
