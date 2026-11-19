<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use Tests\Support\IntegrationTester;

/**
 * Starting a topic (slice 3d): the link on the forum page and the page Forum:Topic:Create. Every test runs in a
 * transaction that is rolled back.
 */
class TopicCreatePagesCest
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

    public function aUserStartsATopicWithItsFirstPost(IntegrationTester $I) : void
    {
        $forum = $this->createForum($I);
        $user = $I->createUser('Starter', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.forum.view/' . $forum->uuid->toString());
        $I->click('.create-topic-link');
        $I->submitForm('#frm-createTopicForm', ['name' => 'Brand new topic', 'text' => 'The *first* post']);

        $I->seeResponseCodeIs(200);
        $I->em()->clear();
        $topic = $I->em()->getRepository(TopicEntity::class)->findOneBy(['forum' => $forum->id, 'name' => 'Brand new topic']);
        $I->assertNotNull($topic);
        $I->seeInCurrentUrl('/forum.topic.view/' . $topic->uuid->toString());
        $I->see('Brand new topic', 'h1');
        $I->seeInSource('<em>first</em>');
        $I->assertSame(1, $I->em()->getRepository(PostEntity::class)->count(['topic' => $topic]));
    }

    public function anEmptyNameShowsTheErrorOfTheForm(IntegrationTester $I) : void
    {
        $forum = $this->createForum($I);
        $user = $I->createUser('Starter', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.topic.create/' . $forum->uuid->toString());
        $I->submitForm('#frm-createTopicForm', ['name' => '   ', 'text' => 'A text long enough']);

        $I->see('Zadejte název tématu');
        $I->assertSame(0, $I->em()->getRepository(TopicEntity::class)->count(['forum' => $forum]));
    }

    public function withoutThePermissionThereIsNoLinkAndThePageIsForbidden(IntegrationTester $I) : void
    {
        $forum = $this->createForum($I);
        $forum->permissions->topicAdd = false;
        $I->em()->flush();
        $user = $I->createUser('NoStarter', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.forum.view/' . $forum->uuid->toString());
        $I->dontSeeElement('.create-topic-link');
        $I->seePageIsRefused('/forum.topic.create/' . $forum->uuid->toString(), 403);
    }

    public function aGuestIsSentToTheLogin(IntegrationTester $I) : void
    {
        $forum = $this->createForum($I);

        $I->amOnPage('/forum.forum.view/' . $forum->uuid->toString());
        $I->dontSeeElement('.create-topic-link');
        $I->amOnPage('/forum.topic.create/' . $forum->uuid->toString());
        $I->seeInCurrentUrl('/web.user.login/');
    }

    private function createForum(IntegrationTester $I) : ForumEntity
    {
        $category = new CategoryEntity();
        $category->name = 'Create category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $I->em()->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'Create forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $I->em()->persist($forum);
        $I->em()->flush();

        return $forum;
    }

}
