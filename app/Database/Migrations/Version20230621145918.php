<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20230621145918 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create users table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.users');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('name', Types::STRING)
            ->setLength(512)
            ->setComment('Name');

        $table->addColumn('surname', Types::STRING)
            ->setLength(512)
            ->setComment('Surname');

        $table->addColumn('username', Types::STRING)
            ->setLength(512)
            ->setComment('Username');

        $table->addColumn('email', Types::STRING)
            ->setLength(512)
            ->setComment('Email');

        $table->addColumn('password', Types::STRING)
            ->setLength(1024)
            ->setComment('Password');

        $table->addColumn('is_active', Types::BOOLEAN)
            ->setComment('Is active?');

        $table->addColumn('avatar', Types::STRING)
            ->setLength(255)
            ->setNotnull(false)
            ->setComment('Avatar: the image id of contributte/imagist (avatars/<name>.webp)');

        $table->addColumn('signature', Types::TEXT)
            ->setNotnull(false)
            ->setComment('Signature');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('rank_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Special rank ID');

        $table->addColumn('language_id', Types::BIGINT)
            ->setComment('Language ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Users')

            ->addIndex(['language_id'], 'K__Users__Language_id')
            ->addIndex(['rank_id'], 'K__Users__Rank_id')
            ->addUniqueIndex(['email'], 'UK__Users__Email')
            ->addUniqueIndex(['username'], 'UK__Users__Username')
            ->addUniqueIndex(['uuid'], 'UK__Users__Uuid')

            ->addForeignKeyConstraint('language', ['language_id'], ['id'], name: 'FK__Users__Language_id')
            ->addForeignKeyConstraint('rank', ['rank_id'], ['id'], options: ['onDelete' => 'SET NULL'], name: 'FK__Users__Rank_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.users');
    }

}
