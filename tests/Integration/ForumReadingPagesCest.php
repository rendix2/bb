<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use Tests\Support\IntegrationTester;

/**
 * Reading the forum (slice 1): category, forum (page of topics) and topic (page of posts), addressed by uuid, through the
 * Nette Application in the test process. Every test runs in a transaction that is rolled back.
 */
class ForumReadingPagesCest
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

    public function theReadingPagesRenderWithWorkingLinks(IntegrationTester $I) : void
    {
        [$category, $forum, $topic] = $this->createTopic($I, 'Reading', 1);

        foreach (['/forum.index/', $this->url('category', $category->uuid->toString()), $this->url('forum', $forum->uuid->toString()), $this->url('topic', $topic->uuid->toString())] as $url) {
            $I->amOnPage($url);
            $I->seeResponseCodeIs(200);
            // an invalid link renders as "#error: ..." (or a warning in debug mode)
            $I->dontSeeInSource('#error');
            $I->dontSeeInSource('Invalid link');
        }

        $I->amOnPage($this->url('forum', $forum->uuid->toString()));
        $I->see($topic->name);
        $I->amOnPage($this->url('category', $category->uuid->toString()));
        $I->see($forum->name);
    }

    public function inactiveOrUnknownPagesAreNotFound(IntegrationTester $I) : void
    {
        [$category, $forum, $topic] = $this->createTopic($I, 'Inactive', 1);

        $I->seePageIsRefused($this->url('topic', '00000000-0000-4000-8000-000000000000'), 404);

        $forum->active = false;
        $I->em()->flush();
        $I->seePageIsRefused($this->url('forum', $forum->uuid->toString()), 404);
        $I->seePageIsRefused($this->url('topic', $topic->uuid->toString()), 404);

        $category->active = false;
        $I->em()->flush();
        $I->seePageIsRefused($this->url('category', $category->uuid->toString()), 404);
    }

    public function viewingATopicCountsTheView(IntegrationTester $I) : void
    {
        [, , $topic] = $this->createTopic($I, 'Views', 1);

        $I->amOnPage($this->url('topic', $topic->uuid->toString()));
        $I->amOnPage($this->url('topic', $topic->uuid->toString()));

        $reloaded = $I->reload(TopicEntity::class, $topic->id);
        $I->assertNotNull($reloaded);
        $I->assertSame(2, $reloaded->viewCount);
    }

    public function postsArePaginated(IntegrationTester $I) : void
    {
        // 4 posts per page (topics.pagination.itemsPerPage)
        [, , $topic, $posts] = $this->createTopic($I, 'Pages', 6);

        $I->amOnPage($this->url('topic', $topic->uuid->toString()));
        $I->seeElement('#post-' . $posts[0]->id);
        $I->dontSeeElement('#post-' . $posts[5]->id);

        $I->amOnPage($this->url('topic', $topic->uuid->toString()) . '?page=2');
        $I->seeElement('#post-' . $posts[5]->id);
        $I->dontSeeElement('#post-' . $posts[0]->id);
    }

    public function theRankFollowsThePostCountUnlessTheUserHasASpecialRank(IntegrationTester $I) : void
    {
        // the user of createTopic has no special rank: with 1 post the rank of 0+ posts (RankFixture: Nováček)
        [, , $topic] = $this->createTopic($I, 'Rank', 1);

        $I->amOnPage($this->url('topic', $topic->uuid->toString()));
        $I->see('Nováček', '.post-rank');
        $I->see('1', '.post-count');
    }

    /**
     * Regression 2026-11-19: the old topic page printed the text and the signature with |noescape.
     */
    public function theTextAndTheSignatureAreEscaped(IntegrationTester $I) : void
    {
        [, , $topic, $posts] = $this->createTopic($I, 'Escape', 1);
        $posts[0]->text = "<script>alert('text')</script>\nsecond line";
        $posts[0]->user->signature = "<img src=x onerror=alert('signature')>";
        $I->em()->flush();

        $I->amOnPage($this->url('topic', $topic->uuid->toString()));
        $I->dontSeeInSource("<script>alert('text')</script>");
        $I->seeInSource('&lt;script&gt;');
        $I->dontSeeInSource("<img src=x onerror");
        $I->seeInSource('&lt;img src=x onerror');
        // new lines become <br>
        $I->seeInSource('<br>');
    }

    public function theForumRulesAreShownEscapedAndOnlyForAnActiveForum(IntegrationTester $I) : void
    {
        [, $forum] = $this->createTopic($I, 'Rules', 1);
        $forum->rules = "Be nice.\n<script>alert('rules')</script>";
        $I->em()->flush();

        $I->amOnPage($this->url('forum', $forum->uuid->toString()));
        $I->click('.forum-rules-link');
        $I->seeResponseCodeIs(200);
        $I->see('Be nice.', '.forum-rules');
        $I->dontSeeInSource("<script>alert('rules')</script>");

        $forum->active = false;
        $I->em()->flush();
        $I->seePageIsRefused('/forum.forum.rules/default/' . $forum->uuid->toString(), 404);
    }

    private function url(string $page, string $uuid) : string
    {
        return '/forum.' . $page . '.view/default/' . $uuid;
    }

    /**
     * A new active category with a forum and a topic of $postCount posts by a new user.
     *
     * @return array{CategoryEntity, ForumEntity, TopicEntity, list<PostEntity>}
     */
    private function createTopic(IntegrationTester $I, string $label, int $postCount) : array
    {
        $em = $I->em();
        $user = $I->createUser($label);

        $category = new CategoryEntity();
        $category->name = $label . ' category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = $label . ' forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = true;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = $label . ' topic ' . random_int(100000, 999999);
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $user;
        $em->persist($topic);

        $posts = [];

        for ($i = 0; $i < $postCount; $i++) {
            $post = new PostEntity();
            $post->user = $user;
            $post->category = $category;
            $post->forum = $forum;
            $post->topic = $topic;
            $post->title = null;
            $post->text = $label . ' post ' . $i;
            $post->addIpAddress = '127.0.0.1';
            $post->editIpAddress = null;
            $em->persist($post);
            $posts[] = $post;
        }

        $em->flush();

        return [$category, $forum, $topic, $posts];
    }

}
