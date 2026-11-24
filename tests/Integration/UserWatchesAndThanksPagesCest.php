<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\CategoryEntity;
use App\Model\Entity\ForumEntity;
use App\Model\Entity\PostEntity;
use App\Model\Entity\ThankEntity;
use App\Model\Entity\TopicEntity;
use App\Model\Entity\TopicWatchEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\TopicWatchService;
use Tests\Support\IntegrationTester;

/**
 * The pages "My watched topics" (only the owner, stop watching by POST + CSRF) and "Thanks received" (who thanked the user for
 * what, for logged-in users, only from the forums the viewer may read) - decisions of 2026-11-20. Every test runs in a transaction
 * that is rolled back.
 */
class UserWatchesAndThanksPagesCest
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
     * Only the own list (with a uuid of another user in the address too), only topics he may still read, a guest gets the login.
     */
    public function myWatchedTopicsSeesOnlyTheOwner(IntegrationTester $I) : void
    {
        $owner = $I->createUser('Watcher', role: 'Normal');
        $other = $I->createUser('Other', role: 'Normal');
        [, $visible] = $this->createTopic($I, $owner, 'Watched forum', true, true);
        [, $inactive] = $this->createTopic($I, $owner, 'Closed watched forum', false, true);
        [, $hidden] = $this->createTopic($I, $owner, 'Watched forum of closed category', true, false);
        [, $foreign] = $this->createTopic($I, $owner, 'Foreign forum', true, true);
        $watches = $I->grabService(TopicWatchService::class);
        $watches->watch($owner, $visible);
        $watches->watch($owner, $inactive);
        $watches->watch($owner, $hidden);
        $watches->watch($other, $foreign);

        $I->amOnPage('/web.user.watches/');
        $I->seeElement('#frm-loginForm');

        $I->login($owner->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.view/');
        $I->click('.profile-watches-link');
        $I->seeResponseCodeIs(200);
        $I->seeElement('a.user-watch-topic[href*="' . $visible->uuid->toString() . '"]');
        // a topic he may not read any more: listed as inaccessible without its name and forum, only to stop watching it
        $I->dontSee($inactive->name);
        $I->dontSee($hidden->name);
        $I->dontSee('Closed watched forum');
        $I->dontSee('Watched forum of closed category');
        $I->seeNumberOfElements('tr.user-watch-inaccessible', 2);
        $I->see('Nepřístupné téma', 'tr.user-watch-inaccessible');
        $I->dontSee($foreign->name);
        $I->submitForm('#frm-unwatchForm-' . $inactive->id, []);
        $I->assertSame(0, $this->watchCount($I, $owner, $inactive));
        $I->amOnPage('/web.user.watches/');
        $I->seeNumberOfElements('tr.user-watch-inaccessible', 1);

        $I->amOnPage('/web.user.watches/' . $other->uuid->toString());
        $I->see($visible->name, '.user-watch-topic');
        $I->dontSee($foreign->name);

        $I->amOnPage('/web.user.view/' . $other->uuid->toString());
        $I->dontSeeElement('.profile-watches-link');
    }

    public function aWatchIsStoppedOnlyWithTheToken(IntegrationTester $I) : void
    {
        $owner = $I->createUser('Watcher', role: 'Normal');
        [, $topic] = $this->createTopic($I, $owner, 'Watched forum', true, true);
        $I->grabService(TopicWatchService::class)->watch($owner, $topic);
        $I->login($owner->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.watches/?do=unwatchForm-' . $topic->id . '-submit');
        $I->amOnPage('/web.user.watches/');
        $I->submitForm('#frm-unwatchForm-' . $topic->id, ['_token_' => 'forged']);
        $I->assertSame(1, $this->watchCount($I, $owner, $topic));

        $I->amOnPage('/web.user.watches/');
        $I->submitForm('#frm-unwatchForm-' . $topic->id, []);
        $I->assertSame(0, $this->watchCount($I, $owner, $topic));
        $I->amOnPage('/web.user.watches/');
        $I->dontSee($topic->name);
    }

    /**
     * Who thanked him and for what: his posts (link to the page of the topic with the anchor) and his topics; not the thanks of
     * posts / topics of others in his topics, not from forums the viewer may not read. The count on the profile is the same.
     */
    public function theThanksReceivedAreOnlyHisAndFromVisibleForums(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $thanker = $I->createUser('Thanker', role: 'Normal');
        $someone = $I->createUser('Someone', role: 'Normal');

        [, $topic, $post] = $this->createTopic($I, $author, 'Open forum', true, true);
        $reply = $this->addPost($I, $topic, $someone);
        $this->thank($I, $thanker, $topic, $post);
        $this->thank($I, $thanker, $topic, null);
        $this->thank($I, $thanker, $topic, $reply);

        [, $otherTopic] = $this->createTopic($I, $someone, 'Forum of someone', true, true);
        $hisReply = $this->addPost($I, $otherTopic, $author);
        $this->thank($I, $someone, $otherTopic, $hisReply);
        $this->thank($I, $thanker, $otherTopic, null);

        [, $closedTopic, $closedPost] = $this->createTopic($I, $author, 'Closed forum', false, true);
        $this->thank($I, $thanker, $closedTopic, $closedPost);
        [, $hiddenTopic] = $this->createTopic($I, $author, 'Forum of closed category', true, false);
        $this->thank($I, $thanker, $hiddenTopic, null);

        $I->amOnPage('/web.user.thanks/' . $author->uuid->toString());
        $I->seeElement('#frm-loginForm');

        $I->login($someone->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.view/' . $author->uuid->toString());
        $I->see('3', '.profile-thank-count');
        $I->click('.profile-thank-count');
        $I->seeResponseCodeIs(200);
        $I->seeInCurrentUrl('/web.user.thanks/' . $author->uuid->toString());

        $I->seeNumberOfElements('.user-thank', 3);
        $I->seeElement('a.user-thank-post[href*="' . $topic->uuid->toString() . '"][href*="#post-' . $post->id . '"]');
        $I->seeElement('a.user-thank-post[href*="#post-' . $hisReply->id . '"]');
        $I->seeElement('a.user-thank-topic[href*="' . $topic->uuid->toString() . '"]');
        $I->dontSeeElement('a[href*="#post-' . $reply->id . '"]');
        $I->dontSeeElement('a.user-thank-topic[href*="' . $otherTopic->uuid->toString() . '"]');
        $I->see($thanker->username, '.user-thank-who');
        $I->see($someone->username, '.user-thank-who');
        $I->dontSee($closedTopic->name);
        $I->dontSee($hiddenTopic->name);
    }

    public function withoutUuidItIsTheOwnThanksPage(IntegrationTester $I) : void
    {
        $author = $I->createUser('Author', role: 'Normal');
        $I->login($author->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.thanks/');
        $I->seeResponseCodeIs(200);
        $I->seeInCurrentUrl($author->uuid->toString());
        $I->dontSeeElement('.user-thank');
    }

    private function watchCount(IntegrationTester $I, UserEntity $user, TopicEntity $topic) : int
    {
        return $I->em()->getRepository(TopicWatchEntity::class)->count(['user' => $user, 'topic' => $topic]);
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
        $post->text = 'A post in ' . $topic->name . ' ' . random_int(100000, 999999);
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
}
