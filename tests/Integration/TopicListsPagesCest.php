<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\FileEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Service\PostWriteException;
use App\Model\Service\TopicWatchService;
use Tests\Support\IntegrationTester;

/**
 * Lists of a topic (slice 3o): who thanked, who watches (moderators only), the attachments. Every test runs in a
 * transaction that is rolled back.
 */
class TopicListsPagesCest
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

    public function theThanksOfTheTopicAndOfItsPostsAreListed(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author);
        $topicThanker = $I->createUser('TopicThanker', role: 'Normal');
        $postThanker = $I->createUser('PostThanker', role: 'Normal');
        $this->thank($I, $topicThanker, $topic, null);
        $this->thank($I, $postThanker, $topic, $post);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('.topic-thanks-link');

        $I->seeInCurrentUrl('/forum.topic.thanks/' . $topic->uuid->toString());
        $I->see($topicThanker->username, '.topic-thanks-list .thank-user');
        $I->dontSee($postThanker->username, '.topic-thanks-list');
        $I->see($postThanker->username, '#post-thanks-' . $post->id . ' .thank-user');
        $I->seeLink($postThanker->username, '/web.user.view/' . $postThanker->uuid->toString());
        $I->seeElement('#post-thanks-' . $post->id . ' a[href$="#post-' . $post->id . '"]');
    }

    public function onlyAModeratorSeesTheWatchers(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic] = $this->createTopic($I, $author);
        $watcher = $I->createUser('Watcher', role: 'Normal');
        $watch = new TopicWatchEntity();
        $watch->user = $watcher;
        $watch->topic = $topic;
        $I->em()->persist($watch);
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $moderatorEntity = new UserModeratorEntity();
        $moderatorEntity->user = $moderator;
        $moderatorEntity->forum = $topic->forum;
        $I->em()->persist($moderatorEntity);
        $I->em()->flush();

        $I->amOnPage('/forum.topic.watchers/' . $topic->uuid->toString());
        $I->seeInCurrentUrl('/web.user.login/');

        $I->login($author->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('.topic-watchers-link');
        $I->seePageIsRefused('/forum.topic.watchers/' . $topic->uuid->toString(), 403);

        $service = $I->grabService(TopicWatchService::class);

        try {
            $service->watchersOf($I->em()->find(UserEntity::class, $author->id), $I->em()->find(TopicEntity::class, $topic->id) ?? $topic);
            $I->fail('The author must not get the watchers.');
        } catch (PostWriteException) {
        }

        $I->login($moderator->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('.topic-watchers-link');
        $I->see($watcher->username, '.topic-watchers .watcher');
    }

    public function allAttachmentsOfTheTopicAreListed(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author);
        $file = new FileEntity();
        $file->user = $author;
        $file->post = $post;
        $file->originalName = 'plan.pdf';
        $file->storedName = 'test-' . $post->id . '.pdf';
        $file->mimeType = 'application/pdf';
        $file->size = 2048;
        $I->em()->persist($file);
        $I->em()->flush();

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('.topic-attachments-link');

        $I->seeInCurrentUrl('/forum.topic.attachments/' . $topic->uuid->toString());
        $I->seeLink('plan.pdf', '/forum.post.attachment/' . $file->uuid->toString());
    }

    private function thank(IntegrationTester $I, UserEntity $user, TopicEntity $topic, ?PostEntity $post) : void
    {
        $thank = new ThankEntity();
        $thank->user = $user;
        $thank->topic = $topic;
        $thank->post = $post;
        $thank->ipAddress = '127.0.0.1';
        $I->em()->persist($thank);
        $I->em()->flush();
    }

    /**
     * @return array{TopicEntity, PostEntity}
     */
    private function createTopic(IntegrationTester $I, UserEntity $author) : array
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Lists category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Lists forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Lists topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post with lists';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return [$topic, $post];
    }
}
