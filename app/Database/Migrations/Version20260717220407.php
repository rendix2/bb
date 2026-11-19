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

final class Version20260717220407 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create ban table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.ban');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('email', Types::STRING)
            ->setLength(512)
            ->setComment('Email');

        $table->addColumn('ip_address', IpAddressType::NAME)
            ->setComment('IP address');

        $table->addColumn('username', Types::STRING)
            ->setLength(512)
            ->setComment('Username');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Bans')

            ->addUniqueIndex(['uuid'], 'UK__Ban__Uuid');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.ban');
    }

}
