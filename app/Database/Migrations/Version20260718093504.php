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

final class Version20260718093504 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'create thank table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('public.thank');

        $table->addColumn('id', Types::BIGINT)
            ->setAutoincrement(true)
            ->setComment('ID');

        $table->addColumn('uuid', UuidType::NAME)
            ->setComment('UUID');

        $table->addColumn('ip_address', IpAddressType::NAME)
            ->setComment('IP Address');

        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setComment('Created at');

        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
            ->setComment('Updated at');

        $table->addColumn('category_id', Types::BIGINT)
            ->setComment('Category ID');

        $table->addColumn('forum_id', Types::BIGINT)
            ->setComment('Forum ID');

        $table->addColumn('topic_id', Types::BIGINT)
            ->setComment('Topic ID');

        $table->addColumn('post_id', Types::BIGINT)
            ->setNotnull(false)
            ->setComment('Post ID (null = thanks for the topic)');

        $table->addColumn('user_id', Types::BIGINT)
            ->setComment('User ID');

        $primaryKey = new PrimaryKeyConstraintEditor();
        $primaryKey->setIsClustered(false);
        $primaryKey->setColumnNames(new UnqualifiedName(Identifier::unquoted('id')));

        $table->addPrimaryKeyConstraint($primaryKey->create())
            ->setComment('Thanks')

            ->addIndex(['category_id'], 'K__Thank__Category_id')
            ->addIndex(['forum_id'], 'K__Thank__Forum_id')
            ->addIndex(['post_id'], 'K__Thank__Post_id')
            ->addIndex(['topic_id'], 'K__Thank__Topic_id')
            ->addIndex(['user_id'], 'K__Thank__User_id')
            ->addUniqueIndex(['user_id', 'post_id'], 'UK__Thank__User_id_post_id')
            ->addUniqueIndex(['user_id', 'topic_id'], 'UK__Thank__User_id_topic_id', options: ['where' => '(post_id IS NULL)'])
            ->addUniqueIndex(['uuid'], 'UK__Thank__Uuid')

            ->addForeignKeyConstraint('category', ['category_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Thank__Category_id')
            ->addForeignKeyConstraint('forum', ['forum_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Thank__Forum_id')
            ->addForeignKeyConstraint('post', ['post_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Thank__Post_id')
            ->addForeignKeyConstraint('topic', ['topic_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Thank__Topic_id')
            ->addForeignKeyConstraint('users', ['user_id'], ['id'], options: ['onDelete' => 'CASCADE'], name: 'FK__Thank__User_id');
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('public.thank');
    }

}
