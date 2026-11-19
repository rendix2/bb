<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20260805075523 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create user_favourite table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.user_favourite');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('favourite_user_id', Types::BIGINT)
            ->setComment('Favourite user ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('User favourite users')

            ->addIndex(['favourite_user_id'], 'K__User_favourite__Favourite_user_id')
            ->addIndex(['user_id'], 'K__User_favourite__User_id')
            ->addUniqueIndex(['uuid'], 'UK__User_favourite__Uuid')

            ->addForeignKeyConstraint('users', ['favourite_user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__User_favourite__Favourite_user_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__User_favourite__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.user_favourite');
    }

}
