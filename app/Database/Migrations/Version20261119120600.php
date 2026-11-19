<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20261119120600 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create user_email_change_request table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.user_email_change_request');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('new_email', Types::STRING)
            ->setLength(512)
            ->setComment('New email');

        $table->addColumn('token_hash', Types::STRING)
            ->setLength(64)
            ->setComment('SHA-256 of the token in the link');

        $table->addColumn('valid_until', Types::DATETIME_IMMUTABLE)
            ->setComment('Link is valid until');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Pending e-mail changes')

            ->addUniqueIndex(['token_hash'], 'UK__User_email_change_request__Token_hash')
            ->addUniqueIndex(['user_id'], 'UK__User_email_change_request__User_id')

            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__User_email_change_request__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.user_email_change_request');
    }

}
