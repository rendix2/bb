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

final class Version20260718094805 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create report table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.report');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('ip_address', IpAddressType::NAME)
            ->setComment('IP Address');

        $table->addColumn('report_text', Types::TEXT)
            ->setComment('Report text');

        $table->addColumn('status', Types::STRING)
            ->setLength(16)
            ->setComment('Status (ReportStatus)');

        $table->addColumn('handled_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Handled at');

        $table->addColumn('moderator_note', Types::TEXT)
            ->setNotnull(false)
            ->setComment('Note of the moderator (only for moderators)');

        $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('topic_id', Types::BIGINT)
            ->setComment('Topic ID');

        $table->addColumn('post_id', Types::BIGINT)
            ->setComment('Post ID');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $table->addColumn('handled_by_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Handled by (user ID)');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Post reports')

            ->addIndex(['handled_by_id'], 'K__Report__Handled_by_id')
            ->addIndex(['post_id'], 'K__Report__Post_id')
            ->addIndex(['topic_id'], 'K__Report__Topic_id')
            ->addIndex(['user_id'], 'K__Report__User_id')
            ->addUniqueIndex(['user_id', 'post_id'], 'UK__Report__User_id_post_id')
            ->addUniqueIndex(['uuid'], 'UK__Report__Uuid')

            ->addForeignKeyConstraint('post', ['post_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Report__Post_id')
            ->addForeignKeyConstraint('topic', ['topic_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Report__Topic_id')
            ->addForeignKeyConstraint('users', ['handled_by_id'], ['id'], options: ['onDelete' => 'SET NULL'], name: 'FK__Report__Handled_by_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Report__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.report');
    }

    public function postUp(Schema $schema) : void
    {
        // a CHECK constraint, DBAL cannot express it: the status is one of App\Model\Enum\ReportStatus (ReportStatusCheckCest compares them)
        $this->connection->executeStatement("ALTER TABLE public.report ADD CONSTRAINT CH__Report__Status CHECK (status IN ('open', 'resolved', 'rejected'))");
    }

}
