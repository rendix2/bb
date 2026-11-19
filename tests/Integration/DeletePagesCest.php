<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserModeratorEntity;
use Tests\Support\IntegrationTester;

/**
 * Deleting posts and topics (slice 3f): confirmed by a form (POST + CSRF), the first post only with its topic.
 * Every test runs in a transaction that is rolled back.
 */
class DeletePagesCest
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

    /**
     * Regression: the old code deleted by a GET link (CSRF); opening the page must not delete anything.
     */
    public function theAuthorDeletesHisReplyOnlyByConfirmingTheForm(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, , $reply] = $this->createTopic($I, $author);
        $replyId = $reply->id;
        $I->login($author->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('#post-' . $replyId . ' .post-delete-link');
        $I->assertNotNull($I->reload(PostEntity::class, $replyId), 'Opening the delete page deleted the post.');

        $I->submitForm('#frm-deleteForm', []);
        $I->seeInCurrentUrl('/forum.topic.view/' . $topic->uuid->toString());
        $I->assertNull($I->reload(PostEntity::class, $replyId));
    }

    public function theFirstPostCannotBeDeletedAlone(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $first] = $this->createTopic($I, $author);
        $I->login($author->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#post-' . $first->id . ' .post-delete-link');
        $I->seePageIsRefused('/forum.post.delete/' . $first->uuid->toString(), 403);
    }

    public function anotherUserCannotDeleteAReply(IntegrationTester $I) : void
    {
        [$topic, , $reply] = $this->createTopic($I, $I->createUser('Author', role: 'Normal'));
        $other = $I->createUser('Other', role: 'Normal');
        $I->login($other->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('.post-delete-link');
        $I->dontSeeElement('.topic-delete-link');
        $I->seePageIsRefused('/forum.post.delete/' . $reply->uuid->toString(), 403);
        $I->seePageIsRefused('/forum.topic.delete/' . $topic->uuid->toString(), 403);
    }

    public function aModeratorDeletesTheWholeTopic(IntegrationTester $I) : void
    {
        [$topic, $first, $reply] = $this->createTopic($I, $I->createUser('Author', role: 'Normal'));
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $moderatorEntity = new UserModeratorEntity();
        $moderatorEntity->user = $moderator;
        $moderatorEntity->forum = $topic->forum;
        $I->em()->persist($moderatorEntity);
        $I->em()->flush();
        [$topicId, $firstId, $replyId, $forumUuid] = [$topic->id, $first->id, $reply->id, $topic->forum->uuid->toString()];
        $I->login($moderator->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('.topic-delete-link');
        $I->submitForm('#frm-deleteForm', []);

        $I->seeInCurrentUrl('/forum.forum.view/' . $forumUuid);
        $I->assertNull($I->reload(TopicEntity::class, $topicId));
        $I->assertNull($I->em()->find(PostEntity::class, $firstId));
        $I->assertNull($I->em()->find(PostEntity::class, $replyId));
    }

    /**
     * @return array{TopicEntity, PostEntity, PostEntity} the topic, its first post and a reply, all by the author
     */
    private function createTopic(IntegrationTester $I, UserEntity $author) : array
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Delete category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Delete forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Delete topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $posts = [];

        foreach (['The first post', 'A reply'] as $text) {
            $post = new PostEntity();
            $post->user = $author;
            $post->category = $category;
            $post->forum = $forum;
            $post->topic = $topic;
            $post->title = null;
            $post->text = $text;
            $post->addIpAddress = '127.0.0.1';
            $post->editIpAddress = null;
            $em->persist($post);
            $em->flush();
            $posts[] = $post;
        }

        return [$topic, $posts[0], $posts[1]];
    }

}
