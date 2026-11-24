<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\PostWriteException;
use App\Model\Service\ThankService;
use Tests\Support\IntegrationTester;

/**
 * Thanks for topics and posts, watching topics (slice 3h). Every test runs in a transaction that is rolled back.
 */
class ThankWatchPagesCest
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

    public function aUserThanksAPostOnceAndNotHisOwn(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author);
        $reader = $I->createUser('Reader', role: 'Normal');
        $I->login($reader->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->submitForm('#frm-thankPostForm-' . $post->id, []);

        $I->see('1', '#post-' . $post->id . ' .post-thank-count');
        $I->dontSeeElement('#frm-thankPostForm-' . $post->id);
        $I->assertSame(1, $I->em()->getRepository(ThankEntity::class)->count(['post' => $post->id]));

        $service = $I->grabService(ThankService::class);
        $this->expectError($I, ThankService::ERROR_ALREADY, fn () => $service->thankPost($I->em()->find(UserEntity::class, $reader->id) ?? $reader, $I->em()->find(PostEntity::class, $post->id) ?? $post, '127.0.0.1'));
        $this->expectError($I, ThankService::ERROR_OWN, fn () => $service->thankPost($I->em()->find(UserEntity::class, $author->id) ?? $author, $I->em()->find(PostEntity::class, $post->id) ?? $post, '127.0.0.1'));
    }

    public function theAuthorSeesNoThankButtonsAtHisOwnTopic(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author);
        $I->login($author->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#frm-thankPostForm-' . $post->id);
        $I->dontSeeElement('#frm-thankTopicForm');
    }

    public function aUserThanksTheTopic(IntegrationTester $I) : void
    {
        [$topic] = $this->createTopic($I, $I->createUser('Author', role: 'Normal'));
        $reader = $I->createUser('Reader', role: 'Normal');
        $I->login($reader->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->submitForm('#frm-thankTopicForm', []);

        $I->see('1', '.topic-thank-count');
        $I->dontSeeElement('#frm-thankTopicForm');
        $I->assertSame(1, $I->em()->getRepository(ThankEntity::class)->count(['topic' => $topic->id, 'post' => null]));
    }

    public function aUserWatchesAndUnwatchesAndAVisitResetsTheNotification(IntegrationTester $I) : void
    {
        [$topic] = $this->createTopic($I, $I->createUser('Author', role: 'Normal'));
        $reader = $I->createUser('Reader', role: 'Normal');
        $I->login($reader->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->submitForm('#frm-watchForm', []);
        $watch = $I->em()->getRepository(TopicWatchEntity::class)->findOneBy(['user' => $reader->id, 'topic' => $topic->id]);
        $I->assertNotNull($watch);

        // a mail was queued (slice 3i); opening the topic again allows the next one
        $watch->isNotified = true;
        $I->em()->flush();
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->em()->clear();
        $watch = $I->em()->getRepository(TopicWatchEntity::class)->findOneBy(['user' => $reader->id, 'topic' => $topic->id]);
        $I->assertNotNull($watch);
        $I->assertFalse($watch->isNotified);

        $I->submitForm('#frm-watchForm', []);
        $I->assertSame(0, $I->em()->getRepository(TopicWatchEntity::class)->count(['user' => $reader->id, 'topic' => $topic->id]));
    }

    public function aGuestHasNoThankOrWatchForms(IntegrationTester $I) : void
    {
        [$topic, $post] = $this->createTopic($I, $I->createUser('Author', role: 'Normal'));

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#frm-thankTopicForm');
        $I->dontSeeElement('#frm-thankPostForm-' . $post->id);
        $I->dontSeeElement('#frm-watchForm');
    }

    private function expectError(IntegrationTester $I, string $message, callable $action) : void
    {
        try {
            $action();
        } catch (PostWriteException $exception) {
            $I->assertSame($message, $exception->getMessage());

            return;
        }

        $I->fail('Expected the error ' . $message . '.');
    }

    /**
     * @return array{TopicEntity, PostEntity}
     */
    private function createTopic(IntegrationTester $I, UserEntity $author) : array
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Thank category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Thank forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Thank topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post to thank for';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return [$topic, $post];
    }

}
