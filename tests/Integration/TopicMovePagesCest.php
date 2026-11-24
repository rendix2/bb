<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\TopicMoveEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use App\Model\Service\TopicMoveService;
use DateTimeImmutable;
use Tests\Support\IntegrationTester;

/**
 * Moving a topic to another forum (slice 6d): a moderator of the forum of the topic (or the admin), into a forum where he
 * may start topics; the old forum shows "moved to ..." for topics.movedNoticeDays days. Every test runs in a transaction
 * that is rolled back.
 */
class TopicMovePagesCest
{
    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
    }

    public function _after(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->rollBack();
        $I->em()->clear();
    }

    public function aModeratorMovesTheTopicAndTheOldForumShowsWhere(IntegrationTester $I) : void
    {
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $source = $this->createForum($I, 'Move source', $moderator);
        $target = $this->createForum($I, 'Move target', null);
        $topic = $this->createTopic($I, $source, $I->createUser('Author', role: 'Normal'));
        $I->login($moderator->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('.topic-move-link');
        $I->seeInCurrentUrl('/forum.topic.move/' . $topic->uuid->toString());
        $I->dontSee('Move source', '#frm-moveForm option');
        $I->submitForm('#frm-moveForm', ['forum' => $target->uuid->toString()]);

        $moved = $I->reload(TopicEntity::class, $topic->id);
        $I->assertSame($target->id, $moved?->forum->id);
        $I->assertSame($target->category?->id, $moved->category->id);
        $move = $I->em()->getRepository(TopicMoveEntity::class)->findOneBy(['topic' => $topic->id]);
        $I->assertSame($source->id, $move?->fromForum->id);
        $I->assertSame($target->id, $move->toForum->id);
        $I->assertSame($moderator->id, $move->user?->id);

        $I->amOnPage('/forum.forum.view/' . $source->uuid->toString());
        $I->see('History of a move', '.topic-moved');
        $I->see('Move target', '.topic-moved');
        $I->amOnPage('/forum.forum.view/' . $target->uuid->toString());
        $I->see('History of a move', '[data-topic="' . $topic->uuid->toString() . '"]');
        $I->dontSeeElement('.topic-moved');
    }

    public function theNoticeEndsAfterTheDaysAndWhenTheTopicComesBack(IntegrationTester $I) : void
    {
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $source = $this->createForum($I, 'Move source', $moderator);
        $target = $this->createForum($I, 'Move target', $moderator);
        $old = $this->createTopic($I, $source, $moderator, 'An old move');
        $back = $this->createTopic($I, $source, $moderator, 'A move back');
        $service = $I->grabService(TopicMoveService::class);

        $service->move($moderator, $old, $target);
        $service->move($moderator, $back, $target);
        $service->move($moderator, $back, $source);
        $I->assertCount(1, $service->recentMovesFrom($source), 'Only the topic that did not come back.');

        foreach ($I->em()->getRepository(TopicMoveEntity::class)->findBy(['topic' => $old->id]) as $move) {
            $move->createdAt = new DateTimeImmutable('-8 days');
        }
        $I->em()->flush();

        $I->assertSame([], $service->recentMovesFrom($source));
        $I->assertCount(1, $service->recentMovesFrom($target), 'The move of the topic back is shown in the target forum.');
    }

    public function aSubforumTakesTheCategoryOfItsRootForum(IntegrationTester $I) : void
    {
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $source = $this->createForum($I, 'Move source', $moderator);
        $root = $this->createForum($I, 'Move root', null);
        $subforum = $this->createForum($I, 'Move subforum', null);
        $subforum->category = null;
        $subforum->parent = $root;
        $I->em()->flush();
        $topic = $this->createTopic($I, $source, $moderator);

        $I->grabService(TopicMoveService::class)->move($moderator, $topic, $subforum);

        $I->assertSame($subforum->id, $topic->forum->id);
        $I->assertSame($root->category?->id, $topic->category->id);
    }

    public function otherUsersCannotMoveAndNotIntoAForumWithoutTopics(IntegrationTester $I) : void
    {
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $source = $this->createForum($I, 'Move source', $moderator);
        $closed = $this->createForum($I, 'Move closed', null);
        $closed->permissions->topicAdd = false;
        $I->em()->flush();
        $topic = $this->createTopic($I, $source, $I->createUser('Author', role: 'Normal'));
        $reader = $I->createUser('Reader', role: 'Normal');
        $service = $I->grabService(TopicMoveService::class);
        $url = '/forum.topic.move/' . $topic->uuid->toString();

        $this->expectError($I, TopicMoveService::ERROR_TARGET, fn () => $service->move($moderator, $topic, $closed));
        $this->expectError($I, TopicMoveService::ERROR_TARGET, fn () => $service->move($moderator, $topic, $source));
        $this->expectError($I, PostWriteService::ERROR_NOT_ALLOWED, fn () => $service->move($reader, $topic, $closed));
        $I->assertNotContains($closed->id, array_map(static fn (ForumEntity $forum) : int => $forum->id, $service->targets($moderator, $topic)));
        $I->assertSame($source->id, $I->reload(TopicEntity::class, $topic->id)?->forum->id);

        $I->amOnPage($url);
        $I->seeInCurrentUrl('/web.user.login');

        $I->login($reader->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('.topic-move-link');
        $I->seePageIsRefused($url, 403);

        $I->loginAsAdmin();
        $I->amOnPage($url);
        $I->seeElement('#frm-moveForm');
    }

    private function createForum(IntegrationTester $I, string $name, ?UserEntity $moderator) : ForumEntity
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Category of ' . $name;
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = $name;
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        if ($moderator !== null) {
            $moderatorEntity = new UserModeratorEntity();
            $moderatorEntity->user = $moderator;
            $moderatorEntity->forum = $forum;
            $em->persist($moderatorEntity);
        }

        $em->flush();

        return $forum;
    }

    private function createTopic(IntegrationTester $I, ForumEntity $forum, UserEntity $author, string $name = 'History of a move') : TopicEntity
    {
        $em = $I->em();

        $topic = new TopicEntity();
        $topic->name = $name;
        $topic->category = $forum->category ?? throw new \LogicException('A forum without a category.');
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'The first post';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return $topic;
    }

    private function expectError(IntegrationTester $I, string $key, callable $call) : void
    {
        try {
            $call();
            $I->fail("Expected the error $key.");
        } catch (PostWriteException $exception) {
            $I->assertSame($key, $exception->getMessage());
        }
    }
}
