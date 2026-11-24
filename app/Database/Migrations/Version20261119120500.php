<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20261119120500 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create file table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.file');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('original_name', Types::STRING)
            ->setLength(255)
            ->setComment('Original name');

        $table->addColumn('stored_name', Types::STRING)
            ->setLength(64)
            ->setComment('Stored name');

        $table->addColumn('mime_type', Types::STRING)
            ->setLength(255)
            ->setComment('MIME type');

        $table->addColumn('size', Types::BIGINT)
            ->setComment('Size in bytes');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('post_id', Types::BIGINT)
            ->setComment('Post ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('File')

            ->addIndex(['post_id'], 'K__File__Post_id')
            ->addIndex(['user_id'], 'K__File__User_id')
            ->addUniqueIndex(['stored_name'], 'UK__File__Stored_name')
            ->addUniqueIndex(['uuid'], 'UK__File__Uuid')

            ->addForeignKeyConstraint('post', ['post_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__File__Post_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__File__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.file');
    }

}
