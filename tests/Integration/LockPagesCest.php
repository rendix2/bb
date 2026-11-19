<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Service\PostWriteException;
use App\Model\Service\PostWriteService;
use Tests\Support\IntegrationTester;

/**
 * Locks (slice 3g): a moderator locks / unlocks a topic and a post by POST forms; others neither see them nor may.
 * Every test runs in a transaction that is rolled back.
 */
class LockPagesCest
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

    public function aModeratorLocksAndUnlocksTheTopic(IntegrationTester $I) : void
    {
        [$topic, $moderator] = $this->createTopicWithModerator($I);
        $I->login($moderator->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->submitForm('#frm-lockTopicForm', []);
        $I->assertTrue($this->reloadTopic($I, $topic)->isLocked);
        $I->see('Téma je zamčené');

        $I->submitForm('#frm-lockTopicForm', []);
        $I->assertFalse($this->reloadTopic($I, $topic)->isLocked);
    }

    public function aModeratorLocksAPostAndItsAuthorLosesTheEditLink(IntegrationTester $I) : void
    {
        [$topic, $moderator, $author, $post] = $this->createTopicWithModerator($I);
        $I->login($moderator->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->submitForm('#frm-lockPostForm-' . $post->id, []);
        $I->em()->clear();
        $reloaded = $I->em()->find(PostEntity::class, $post->id);
        $I->assertNotNull($reloaded);
        $I->assertTrue($reloaded->isLocked);

        $I->login($author->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->seeElement('#post-' . $post->id . ' .post-locked');
        $I->dontSeeElement('#post-' . $post->id . ' .post-edit-link');
    }

    public function othersSeeNoLockFormsAndMayNotLock(IntegrationTester $I) : void
    {
        [$topic, , $author, $post] = $this->createTopicWithModerator($I);
        $I->login($author->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#frm-lockTopicForm');
        $I->dontSeeElement('.post-lock-form');

        $service = $I->grabService(PostWriteService::class);
        foreach ([fn () => $service->setTopicLocked($author, $topic, true), fn () => $service->setPostLocked($author, $post, true)] as $lock) {
            try {
                $lock();
                $I->fail('A user who does not moderate locked something.');
            } catch (PostWriteException $exception) {
                $I->assertSame(PostWriteService::ERROR_NOT_ALLOWED, $exception->getMessage());
            }
        }
    }

    private function reloadTopic(IntegrationTester $I, TopicEntity $topic) : TopicEntity
    {
        $reloaded = $I->reload(TopicEntity::class, $topic->id);
        $I->assertNotNull($reloaded);

        return $reloaded;
    }

    /**
     * @return array{TopicEntity, UserEntity, UserEntity, PostEntity} topic, its moderator, the author, his post
     */
    private function createTopicWithModerator(IntegrationTester $I) : array
    {
        $em = $I->em();
        $author = $I->createUser('Author', role: 'Normal');
        $moderator = $I->createUser('Moderator', role: 'Normal');

        $category = new CategoryEntity();
        $category->name = 'Lock category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Lock forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $moderatorEntity = new UserModeratorEntity();
        $moderatorEntity->user = $moderator;
        $moderatorEntity->forum = $forum;
        $em->persist($moderatorEntity);

        $topic = new TopicEntity();
        $topic->name = 'Lock topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->category = $category;
        $post->forum = $forum;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post of the author';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return [$topic, $moderator, $author, $post];
    }

}
