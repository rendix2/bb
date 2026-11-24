<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use Tests\Support\IntegrationTester;

/**
 * "Jump to a forum" (slice 3p): a GET form on the topic and forum pages offering active forums of active categories.
 * Every test runs in a transaction that is rolled back.
 */
class ForumJumpCest
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

    public function theTopicPageJumpsToAnotherForum(IntegrationTester $I) : void
    {
        $category = $this->createCategory($I, 'Jump category', true);
        $forum = $this->createForum($I, $category, 'Jump forum', true);
        $target = $this->createForum($I, $category, 'Jump target', true);
        $hidden = $this->createForum($I, $category, 'Jump inactive', false);
        $inactiveCategory = $this->createCategory($I, 'Jump hidden category', false);
        $inHiddenCategory = $this->createForum($I, $inactiveCategory, 'Jump in hidden category', true);
        $topic = $this->createTopic($I, $forum);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->seeOptionIsSelected('#frm-forumJumpForm select[name=forum]', 'Jump forum');
        $I->seeElement('#frm-forumJumpForm option[value="' . $target->uuid->toString() . '"]');
        $I->dontSeeElement('#frm-forumJumpForm option[value="' . $hidden->uuid->toString() . '"]');
        $I->dontSeeElement('#frm-forumJumpForm option[value="' . $inHiddenCategory->uuid->toString() . '"]');
        $I->seeElement('form#frm-forumJumpForm[method=get]');

        $I->submitForm('#frm-forumJumpForm', ['forum' => $target->uuid->toString()]);
        $I->seeInCurrentUrl('/forum.forum.view/' . $target->uuid->toString());
        $I->seeOptionIsSelected('#frm-forumJumpForm select[name=forum]', 'Jump target');
    }

    private function createCategory(IntegrationTester $I, string $name, bool $active) : CategoryEntity
    {
        $category = new CategoryEntity();
        $category->name = $name;
        $category->sortOrder = 1000;
        $category->active = $active;
        $category->parent = null;
        $I->em()->persist($category);
        $I->em()->flush();

        return $category;
    }

    private function createForum(IntegrationTester $I, CategoryEntity $category, string $name, bool $active) : ForumEntity
    {
        $forum = new ForumEntity();
        $forum->name = $name;
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = $active;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $I->em()->persist($forum);
        $I->em()->flush();

        return $forum;
    }

    private function createTopic(IntegrationTester $I, ForumEntity $forum) : TopicEntity
    {
        $author = $I->createUser('Author', role: 'Normal');
        $category = $forum->category ?? $I->fail('The forum has no category.');

        $topic = new TopicEntity();
        $topic->name = 'Jump topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $I->em()->persist($topic);

        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $I->em()->persist($post);
        $I->em()->flush();

        return $topic;
    }
}
