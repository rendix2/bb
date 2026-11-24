<?php declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Name\Identifier;
use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraintEditor;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20261119121100 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create faq_translation table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.faq_translation');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('question', Types::TEXT)
            ->setComment('Question');

        $table->addColumn('answer', Types::TEXT)
            ->setComment('Answer (Markdown)');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('faq_id', Types::BIGINT)
            ->setComment('FAQ ID');

        $table->addColumn('language_id', Types::BIGINT)
            ->setComment('Language ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Texts of the FAQ in the languages')

            ->addIndex(['faq_id'], 'K__Faq_translation__Faq_id')
            ->addIndex(['language_id'], 'K__Faq_translation__Language_id')
            ->addUniqueIndex(['faq_id', 'language_id'], 'UK__Faq_translation__Faq_id_language_id')
            ->addUniqueIndex(['uuid'], 'UK__Faq_translation__Uuid')

            ->addForeignKeyConstraint('faq', ['faq_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Faq_translation__Faq_id')
            ->addForeignKeyConstraint('language', ['language_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Faq_translation__Language_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.faq_translation');
    }

}
