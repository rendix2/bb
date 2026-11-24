<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20261119121200 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create mass_mail table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.mass_mail');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('subject', Types::STRING)
            ->setLength(1024)
            ->setComment('Subject');

        $table->addColumn('body', Types::TEXT)
            ->setComment('Text (Markdown)');

        $table->addColumn('recipient_count', Types::INTEGER)
            ->setComment('Number of recipients');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('sent_by_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Sent by (user ID)');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Mass e-mails of the administration')

            ->addIndex(['sent_by_id'], 'K__Mass_mail__Sent_by_id')
            ->addUniqueIndex(['uuid'], 'UK__Mass_mail__Uuid')

            ->addForeignKeyConstraint('users', ['sent_by_id'], ['id'], options: ['onDelete' => 'SET NULL'], name: 'FK__Mass_mail__Sent_by_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.mass_mail');
    }

}
