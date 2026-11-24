<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use Tests\Support\IntegrationTester;

/**
 * The list of users (slice 4g): only for logged-in users, filter by a part of the username (case-insensitive), sorting, pages of 25,
 * post counts only from the forums the viewer may read (as on the profile), never the name and the surname. Rolled back after each test.
 */
class UserListPagesCest
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

    public function aGuestGoesToTheLoginAndBack(IntegrationTester $I) : void
    {
        $viewer = $I->createUser('Viewer', role: 'Normal');

        $I->amOnPage('/forum.index/');
        $I->dontSeeElement('.user-list-link');

        $I->amOnPage('/web.user.list/');
        $I->seeElement('#frm-loginForm');
        $I->submitForm('#frm-loginForm', ['username' => $viewer->username, 'password' => IntegrationTester::PASSWORD]);
        $I->seeElement('.user-list');

        $I->amOnPage('/forum.index/');
        $I->click('.user-list-link');
        $I->seeElement('.user-list');
    }

    public function theFilterIsCaseInsensitiveAndShowsNoRealName(IntegrationTester $I) : void
    {
        $tag = 'Žluť' . random_int(100000, 999999);
        $found = $this->createNamed($I, $tag . 'Kůň');
        $other = $this->createNamed($I, 'Other' . random_int(100000, 999999));
        $inactive = $I->createUser('Inactive', false);
        $inactive->username = $tag . 'Off';
        $I->em()->flush();
        $I->login($other->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.list/');
        $I->submitForm('.user-list-filter', ['username' => mb_strtoupper($tag)]);
        $I->seeInCurrentUrl('filter=');
        $I->seeNumberOfElements('.user-list-item', 1);
        $I->see($found->username, '.user-list-username');
        $I->dontSee($inactive->username);
        $I->dontSee($found->name . ' ' . $found->surname);

        // % and _ are literal, not wildcards
        $I->amOnPage('/web.user.list/?filter=' . urlencode('%'));
        $I->see('Žádný uživatel.');
    }

    public function thePostCountIsOnlyFromReadableForumsAndSorts(IntegrationTester $I) : void
    {
        $tag = 'Sort' . random_int(100000, 999999);
        $few = $this->createNamed($I, $tag . 'a');
        $many = $this->createNamed($I, $tag . 'b');
        $visible = $this->createTopic($I, $few, true);
        $hidden = $this->createTopic($I, $many, false);
        $this->addPost($I, $visible, $few);
        $this->addPost($I, $visible, $many);
        $this->addPost($I, $visible, $many);
        // posts in a closed forum: not counted (the profile does not count them either)
        foreach (range(1, 5) as $ignored) {
            $this->addPost($I, $hidden, $few);
        }

        $I->login($few->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.list/?filter=' . $tag . '&sort=posts&order=desc');
        $I->see($many->username, 'tr.user-list-item:nth-child(1) .user-list-username');
        $I->see('2', 'tr.user-list-item:nth-child(1) .user-list-posts');
        $I->see('1', 'tr.user-list-item:nth-child(2) .user-list-posts');

        $I->amOnPage('/web.user.list/?filter=' . $tag . '&sort=username&order=desc');
        $I->see($many->username, 'tr.user-list-item:nth-child(1) .user-list-username');
        $I->amOnPage('/web.user.list/?filter=' . $tag . '&sort=username&order=asc');
        $I->see($few->username, 'tr.user-list-item:nth-child(1) .user-list-username');

        $I->amOnPage('/web.user.list/?filter=' . $tag . '&sort=registered&order=desc');
        $I->see($many->username, 'tr.user-list-item:nth-child(1) .user-list-username');

        // an unknown sorting goes back to the default
        $I->amOnPage('/web.user.list/?sort=password');
        $I->dontSeeInCurrentUrl('password');
        $I->seeElement('.user-list');
    }

    public function theListHasPagesOf25(IntegrationTester $I) : void
    {
        $tag = 'Page' . random_int(100000, 999999);

        foreach (range(1, 26) as $number) {
            $last = $this->createNamed($I, $tag . sprintf('%02d', $number));
        }

        $I->login($last->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.list/?filter=' . $tag);
        $I->seeNumberOfElements('.user-list-item', 25);
        $I->click('2', '.pagination');
        $I->seeNumberOfElements('.user-list-item', 1);
        $I->see($tag . '26', '.user-list-username');
    }

    private function createNamed(IntegrationTester $I, string $username) : UserEntity
    {
        $user = $I->createUser('List', role: 'Normal');
        $user->username = $username;
        $user->name = 'Secret' . random_int(100000, 999999);
        $I->em()->flush();

        return $user;
    }

    private function createTopic(IntegrationTester $I, UserEntity $author, bool $forumActive) : TopicEntity
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'List category';
        $category->sortOrder = 1000;
        $category->active = true;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = 'List forum';
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = $forumActive;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'List topic';
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);
        $em->flush();

        return $topic;
    }

    private function addPost(IntegrationTester $I, TopicEntity $topic, UserEntity $author) : void
    {
        $post = new PostEntity();
        $post->user = $author;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post of the list';
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $I->em()->persist($post);
        $I->em()->flush();
    }
}
