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

final class Version20260718095044 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create session table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.session');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('key', Types::STRING)
            ->setLength(512)
            ->setComment('Key');

        $table->addColumn('ip_address', IpAddressType::NAME)
            ->setComment('IP Address');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('last_activity', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Last activity');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Sessions')

            ->addIndex(['user_id'], 'K__Session__User_id')
            ->addUniqueIndex(['uuid'], 'UK__Session__Uuid')

            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Session__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.session');
    }

}
