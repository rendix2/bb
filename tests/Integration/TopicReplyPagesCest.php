<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use Tests\Support\IntegrationTester;

/**
 * Replying (slice 3c): the fast reply form on the topic page and the reply page with a title, through the Nette
 * Application in the test process. Every test runs in a transaction that is rolled back.
 */
class TopicReplyPagesCest
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

    public function aGuestSeesNoReplyFormAndIsSentToTheLogin(IntegrationTester $I) : void
    {
        $topic = $this->createTopic($I);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#frm-fastReplyForm');
        $I->dontSeeElement('.reply-link');

        $I->amOnPage('/forum.topic.reply/' . $topic->uuid->toString());
        $I->seeInCurrentUrl('/web.user.login/');
    }

    public function aUserRepliesFastFromTheTopicPage(IntegrationTester $I) : void
    {
        $topic = $this->createTopic($I);
        $user = $I->createUser('FastReply', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->submitForm('#frm-fastReplyForm', ['text' => 'A **fast** reply']);

        $I->seeResponseCodeIs(200);
        // the flash message does not survive the redirect in the test process (it does over HTTP, checked with curl)
        $post = $this->lastPostOf($I, $topic);
        $I->assertSame($user->id, $post->user->id);
        $I->assertSame('A **fast** reply', $post->text);
        $I->seeElement('#post-' . $post->id);
        $I->seeInSource('<strong>fast</strong>');
    }

    public function aTooShortReplyShowsTheErrorAndIsNotSaved(IntegrationTester $I) : void
    {
        $topic = $this->createTopic($I);
        $user = $I->createUser('ShortReply', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->submitForm('#frm-fastReplyForm', ['text' => 'abc']);

        $I->see('Text příspěvku je příliš krátký.');
        $I->assertSame(1, $I->em()->getRepository(PostEntity::class)->count(['topic' => $topic]));
    }

    public function aUserRepliesWithATitleOnTheReplyPage(IntegrationTester $I) : void
    {
        $topic = $this->createTopic($I);
        $user = $I->createUser('Reply', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('.reply-link');
        $I->submitForm('#frm-replyForm', ['title' => 'My title', 'text' => 'A reply with a title']);

        // the flash message does not survive the redirect in the test process (it does over HTTP, checked with curl)
        $post = $this->lastPostOf($I, $topic);
        $I->assertSame('My title', $post->title);
        $I->seeElement('#post-' . $post->id);
        $I->seeInCurrentUrl('/forum.topic.view/' . $topic->uuid->toString());
    }

    public function aLockedTopicHasNoReplyFormAndTheReplyPageIsForbidden(IntegrationTester $I) : void
    {
        $topic = $this->createTopic($I);
        $topic->isLocked = true;
        $I->em()->flush();
        $user = $I->createUser('Locked', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#frm-fastReplyForm');
        $I->see('Téma je zamčené');

        $I->seePageIsRefused('/forum.topic.reply/' . $topic->uuid->toString(), 403);
    }

    public function thePreviewShowsTheTextAndSavesNothing(IntegrationTester $I) : void
    {
        $topic = $this->createTopic($I);
        $user = $I->createUser('Preview', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.reply/' . $topic->uuid->toString());
        $I->fillField('text', 'A *previewed* reply');
        $I->click('preview');

        $I->seeInSource('<em>previewed</em>');
        $I->seeElement('.post-preview');
        $I->seeInField('text', 'A *previewed* reply');
        $I->assertSame(1, $I->em()->getRepository(PostEntity::class)->count(['topic' => $topic]));
    }

    public function aQuoteFillsTheReplyWithTheQuotedPost(IntegrationTester $I) : void
    {
        $topic = $this->createTopic($I);
        $user = $I->createUser('Quoter', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('.post-quote-link');

        $I->seeInField('text', "> **" . $topic->user->username . "** napsal:\n>\n> First post of the topic\n\n");
    }

    private function lastPostOf(IntegrationTester $I, TopicEntity $topic) : PostEntity
    {
        $I->em()->clear();
        $post = $I->em()->getRepository(PostEntity::class)->findOneBy(['topic' => $topic->id], ['id' => 'DESC']);
        $I->assertNotNull($post);

        return $post;
    }

    private function createTopic(IntegrationTester $I) : TopicEntity
    {
        $em = $I->em();
        $author = $I->createUser('Author', role: 'Normal');

        $category = new CategoryEntity();
        $category->name = 'Reply category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Reply forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Reply topic';
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
        $post->text = 'First post of the topic';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        return $topic;
    }

}
