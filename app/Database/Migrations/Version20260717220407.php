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
            ->setNotnull(false)
            ->setComment('Banned e-mail');

        $table->addColumn('ip_address', IpAddressType::NAME)
            ->setNotnull(false)
            ->setComment('Banned IP address');

        $table->addColumn('scope', Types::STRING)
            ->setLength(16)
            ->setComment('What the ban blocks (BanScope)');

        $table->addColumn('reason', Types::TEXT)
            ->setComment('Reason shown to the banned visitor');

        $table->addColumn('expires_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Expires at (null = for ever)');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('user_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Banned user ID');

        $table->addColumn('created_by_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Banned by (user ID)');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Bans')

            ->addIndex(['created_by_id'], 'K__Ban__Created_by_id')
            ->addIndex(['user_id'], 'K__Ban__User_id')
            ->addUniqueIndex(['uuid'], 'UK__Ban__Uuid')

            ->addForeignKeyConstraint('users', ['created_by_id'], ['id'], options: ['onDelete' => 'SET NULL'], name: 'FK__Ban__Created_by_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Ban__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.ban');
    }

    public function postUp(Schema $schema) : void
    {
        // a CHECK constraint, DBAL cannot express it: a ban has a target (a user, an e-mail or an IP address)
        $this->connection->executeStatement("ALTER TABLE public.ban ADD CONSTRAINT CH__Ban__Target CHECK (user_id IS NOT NULL OR email IS NOT NULL OR ip_address IS NOT NULL)");
        // a CHECK constraint, DBAL cannot express it: the scope is one of App\Model\Enum\BanScope (SchemaConstraintsCest compares them)
        $this->connection->executeStatement("ALTER TABLE public.ban ADD CONSTRAINT CH__Ban__Scope CHECK (scope IN ('web', 'login', 'registration', 'writing'))");
    }

}
