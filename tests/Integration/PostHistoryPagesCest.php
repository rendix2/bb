<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserModeratorEntity;
use App\Model\Service\PostHistoryService;
use App\Model\Service\PostWriteService;
use Tests\Support\IntegrationTester;

/**
 * The edit history of a post (slice 6c): the author, the moderators of the forum and the admin see it, nobody else. Every
 * test runs in a transaction that is rolled back.
 */
class PostHistoryPagesCest
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

    public function theAuthorSeesThePreviousVersionsTheLastReplacedFirst(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author, null);
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $this->moderate($I, $moderator, $topic->forum);

        $service = $I->grabService(PostWriteService::class);
        $service->edit($author, $post, 'A title', 'The second text', '127.0.0.1');
        $service->edit($this->find($I, $moderator), $this->findPost($I, $post), null, 'The third text', '127.0.0.1');

        $I->login($author->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click('#post-' . $post->id . ' .post-history-link');

        $I->seeInCurrentUrl('/forum.post.history/' . $post->uuid->toString());
        $I->see('The third text', '.post-history-current');
        $I->seeNumberOfElements('.post-history-version', 2);
        // the last replaced version first: the second text replaced by the moderator, then the original by the author
        $I->see('The second text', '.post-history-version-1');
        $I->see('A title', '.post-history-version-1');
        $I->see($moderator->username, '.post-history-version-1 .card-header');
        $I->see('The original text', '.post-history-version-2');
        $I->see($author->username, '.post-history-version-2 .card-header');
    }

    public function theModeratorsOfTheForumAndTheAdminSeeItOthersDoNot(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author, 'The edited text');
        $moderator = $I->createUser('Moderator', role: 'Normal');
        $this->moderate($I, $moderator, $topic->forum);
        $otherModerator = $I->createUser('OtherModerator', role: 'Normal');
        [$otherTopic] = $this->createTopic($I, $otherModerator, null);
        $this->moderate($I, $otherModerator, $otherTopic->forum);
        $reader = $I->createUser('Reader', role: 'Normal');

        $history = $I->grabService(PostHistoryService::class);
        $post = $this->findPost($I, $post);
        $I->assertTrue($history->mayView($this->find($I, $author), $post));
        $I->assertTrue($history->mayView($this->find($I, $moderator), $post));
        $I->assertFalse($history->mayView($this->find($I, $otherModerator), $post));
        $I->assertFalse($history->mayView($this->find($I, $reader), $post));
        $I->assertFalse($history->mayView(null, $post));

        $url = '/forum.post.history/' . $post->uuid->toString();

        $I->login($moderator->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->seeElement('#post-' . $post->id . ' .post-history-link');
        $I->amOnPage($url);
        $I->see('The original text', '.post-history-version');

        $I->login($reader->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->seeElement('#post-' . $post->id . ' .post-edited');
        $I->dontSeeElement('#post-' . $post->id . ' .post-history-link');
        $I->seePageIsRefused($url, 403);

        $I->login($otherModerator->username, IntegrationTester::PASSWORD);
        $I->seePageIsRefused($url, 403);

        $I->loginAsAdmin();
        $I->amOnPage($url);
        $I->see('The original text', '.post-history-version');
    }

    public function aGuestIsSentToTheLogin(IntegrationTester $I) : void
    {
        [, $post] = $this->createTopic($I, $I->createUser('Author', role: 'Normal'), 'The edited text');

        $I->amOnPage('/forum.post.history/' . $post->uuid->toString());

        $I->seeInCurrentUrl('/web.user.login');
    }

    public function aPostWithoutEditsHasNoLink(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        [$topic, $post] = $this->createTopic($I, $author, null);
        $I->login($author->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->dontSeeElement('#post-' . $post->id . ' .post-history-link');

        $I->amOnPage('/forum.post.history/' . $post->uuid->toString());
        $I->seeElement('.post-history-empty');
    }

    /**
     * @return array{TopicEntity, PostEntity}
     */
    private function createTopic(IntegrationTester $I, UserEntity $author, ?string $editedText) : array
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'History category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'History forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'History topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'The original text';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $em->persist($post);

        $em->flush();

        if ($editedText !== null) {
            $I->grabService(PostWriteService::class)->edit($author, $post, null, $editedText, '127.0.0.1');
        }

        return [$topic, $post];
    }

    private function moderate(IntegrationTester $I, UserEntity $user, ForumEntity $forum) : void
    {
        $moderator = new UserModeratorEntity();
        $moderator->user = $this->find($I, $user);
        $moderator->forum = $forum;
        $I->em()->persist($moderator);
        $I->em()->flush();
    }

    private function find(IntegrationTester $I, UserEntity $user) : UserEntity
    {
        return $I->em()->find(UserEntity::class, $user->id) ?? throw new \LogicException('The user is gone.');
    }

    private function findPost(IntegrationTester $I, PostEntity $post) : PostEntity
    {
        return $I->em()->find(PostEntity::class, $post->id) ?? throw new \LogicException('The post is gone.');
    }
}
