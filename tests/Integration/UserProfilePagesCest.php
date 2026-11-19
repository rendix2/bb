<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\UserEntity;
use App\Model\Entity\UserModeratorEntity;
use Tests\Support\IntegrationTester;

/**
 * The profile of the forum (slice 4b): only for logged-in users, the real name only for the user himself and the admin, the counts
 * and the moderated forums only from the forums the viewer may read. Every test runs in a transaction that is rolled back.
 */
class UserProfilePagesCest
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
     * Decision of 2026-11-19: the profiles only for logged-in users. A guest gets the login and after it the profile he asked for.
     */
    public function aGuestIsSentToTheLoginAndBackToTheProfile(IntegrationTester $I) : void
    {
        $user = $I->createUser('Shown', role: 'Normal');
        $reader = $I->createUser('Reader', role: 'Normal');

        $I->amOnPage('/web.user.view/' . $user->uuid->toString());
        $I->seeElement('#frm-loginForm');
        $I->dontSee($user->username);

        $I->submitForm('#frm-loginForm', ['username' => $reader->username, 'password' => IntegrationTester::PASSWORD]);
        $I->seeResponseCodeIs(200);
        $I->see($user->username, '.profile-username');
    }

    public function anUnknownProfileIsNotFound(IntegrationTester $I) : void
    {
        $reader = $I->createUser('Reader', role: 'Normal');
        $I->login($reader->username, IntegrationTester::PASSWORD);

        $I->seePageIsRefused('/web.user.view/00000000-0000-4000-8000-000000000000', 404);
    }

    /**
     * Also the initials: the old template cut the first BYTE (substr), "Č" became an invalid character.
     */
    public function withoutUuidItIsTheOwnProfileWithTheRealName(IntegrationTester $I) : void
    {
        $user = $I->createUser('Čown', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.view/');
        $I->seeResponseCodeIs(200);
        $I->seeInCurrentUrl($user->uuid->toString());
        $I->see($user->username, '.profile-username');
        $I->see('In Process', '.profile-real-name');
        $I->seeElement('.profile-edit-link');
        $I->see('Č', '.profile-initials');
    }

    public function theRealNameIsHiddenFromOthersButNotFromTheAdmin(IntegrationTester $I) : void
    {
        $user = $I->createUser('Private', role: 'Normal');
        $reader = $I->createUser('Reader', role: 'Normal');
        $I->login($reader->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.view/' . $user->uuid->toString());
        $I->see($user->username, '.profile-username');
        $I->dontSeeElement('.profile-real-name');
        $I->dontSee('Process');
        $I->dontSeeElement('.profile-edit-link');

        $admin = $I->createUser('Admin', role: 'Admin');
        $I->login($admin->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.view/' . $user->uuid->toString());
        $I->see('In Process', '.profile-real-name');
    }

    /**
     * The counts (posts, topics, thanks received for posts and topics) and the moderated forums: an inactive forum and a forum of
     * an inactive category are not counted nor named (the old profile counted everything).
     */
    public function theCountsAndModeratedForumsAreOnlyFromVisibleForums(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $thanker = $I->createUser('Thanker', role: 'Normal');

        [$visibleForum, $visibleTopic, $visiblePost] = $this->createTopic($I, $author, 'Visible forum', true, true);
        $this->addPost($I, $visibleTopic, $author);
        $this->thank($I, $thanker, $visibleTopic, $visiblePost);
        $this->thank($I, $thanker, $visibleTopic, null);
        $this->moderate($I, $author, $visibleForum);

        [$inactiveForum, $inactiveTopic, $inactivePost] = $this->createTopic($I, $author, 'Inactive forum', false, true);
        $this->thank($I, $thanker, $inactiveTopic, $inactivePost);
        $this->moderate($I, $author, $inactiveForum);

        [$hiddenCategoryForum, $hiddenTopic, $hiddenPost] = $this->createTopic($I, $author, 'Forum of inactive category', true, false);
        $this->thank($I, $thanker, $hiddenTopic, $hiddenPost);
        $this->moderate($I, $author, $hiddenCategoryForum);

        $I->login($thanker->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.view/' . $author->uuid->toString());
        $I->seeResponseCodeIs(200);

        $I->see('2', '.profile-post-count');
        $I->see('1', '.profile-topic-count');
        $I->see('2', '.profile-thank-count');
        $I->see('Visible forum', '.profile-moderated-forums');
        $I->dontSee('Inactive forum');
        $I->dontSee('Forum of inactive category');
        $I->see($author->createdAt->format('j. n. Y'), '.profile-registered');
    }

    /**
     * The links to the profile in the forum lead to Web:User:View with the uuid; a guest on them gets the login, not an error.
     */
    public function theAuthorLinkOfATopicLeadsAGuestToTheLogin(IntegrationTester $I) : void
    {
        $author = $I->createUser('Linked', role: 'Normal');
        [, $topic] = $this->createTopic($I, $author, 'Linked forum', true, true);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click($author->username, '.post-author');
        $I->seeResponseCodeIs(200);
        $I->seeElement('#frm-loginForm');
    }

    /**
     * @return array{ForumEntity, TopicEntity, PostEntity}
     */
    private function createTopic(IntegrationTester $I, UserEntity $author, string $forumName, bool $forumActive, bool $categoryActive) : array
    {
        $em = $I->em();

        $category = new CategoryEntity();
        $category->name = 'Category of ' . $forumName;
        $category->sortOrder = 1000;
        $category->active = $categoryActive;
        $category->parent = null;
        $em->persist($category);

        $forum = new ForumEntity();
        $forum->name = $forumName;
        $forum->description = null;
        $forum->category = $category;
        $forum->parent = null;
        $forum->active = $forumActive;
        $forum->rules = null;
        $forum->sortOrder = 1;
        $em->persist($forum);

        $topic = new TopicEntity();
        $topic->name = 'Topic in ' . $forumName;
        $topic->category = $category;
        $topic->forum = $forum;
        $topic->user = $author;
        $em->persist($topic);
        $em->flush();

        return [$forum, $topic, $this->addPost($I, $topic, $author)];
    }

    private function addPost(IntegrationTester $I, TopicEntity $topic, UserEntity $author) : PostEntity
    {
        $post = new PostEntity();
        $post->user = $author;
        $post->category = $topic->category;
        $post->forum = $topic->forum;
        $post->topic = $topic;
        $post->title = null;
        $post->text = 'A post in ' . $topic->name;
        $post->addIpAddress = '127.0.0.1';
        $post->editIpAddress = null;
        $I->em()->persist($post);
        $I->em()->flush();

        return $post;
    }

    private function thank(IntegrationTester $I, UserEntity $thanker, TopicEntity $topic, ?PostEntity $post) : void
    {
        $thank = new ThankEntity();
        $thank->user = $thanker;
        $thank->category = $topic->category;
        $thank->forum = $topic->forum;
        $thank->topic = $topic;
        $thank->post = $post;
        $thank->ipAddress = '127.0.0.1';
        $I->em()->persist($thank);
        $I->em()->flush();
    }

    private function moderate(IntegrationTester $I, UserEntity $user, ForumEntity $forum) : void
    {
        $moderator = new UserModeratorEntity();
        $moderator->user = $user;
        $moderator->forum = $forum;
        $I->em()->persist($moderator);
        $I->em()->flush();
    }

}
