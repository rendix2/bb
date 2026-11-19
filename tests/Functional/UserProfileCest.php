<?php declare(strict_types=1);

namespace Tests\Functional;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\TopicEntity;
use Tests\Support\FunctionalTester;

/**
 * The profile of the forum over HTTP (slice 4b): the links to the profiles in the forum work for a guest (he gets the login, not a
 * 500) and for a logged-in user (the profile), and the forum layout links the own profile. The settings of 4d.
 */
class UserProfileCest
{
    /** @var array{int, int}|null the topic opened by the test and its view count before (a GET of the topic counts a view) */
    private ?array $viewedTopic = null;

    public function _after(FunctionalTester $I) : void
    {
        if ($this->viewedTopic === null) {
            return;
        }

        [$id, $viewCount] = $this->viewedTopic;
        $this->viewedTopic = null;

        $I->grabService(EntityManagerDecorator::class)->getRepository(TopicEntity::class)
            ->createQueryBuilder('_t')
            ->update()
            ->set('_t.viewCount', ':viewCount')
            ->where('_t.id = :id')
            ->setParameter('viewCount', $viewCount)
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function aGuestFollowingTheAuthorOfATopicGetsTheLogin(FunctionalTester $I) : void
    {
        $topic = $this->anyTopic($I);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->seePageIsOk('topic');
        $I->click($topic->user->username, '.post-author');
        $I->seePageIsOk('profile of a guest');
        $I->seeElement('#frm-loginForm');
    }

    public function aGuestOnTheIndexLinksGetsTheLogin(FunctionalTester $I) : void
    {
        $I->amOnPage('/forum.index/');
        $I->seePageIsOk('index');
        $links = $I->grabMultiple('a[href*="/web.user.view/"]', 'href');
        $I->assertNotEmpty($links, 'The index of the fixtures links no profile.');

        $I->amOnPage((string) $links[0]);
        $I->seePageIsOk('profile from the index');
        $I->seeElement('#frm-loginForm');
    }

    public function aLoggedInUserSeesTheProfileAndHisOwnFromTheLayout(FunctionalTester $I) : void
    {
        $topic = $this->anyTopic($I);
        $user = $I->createUser('profile');
        $I->login($user->username, FunctionalTester::PASSWORD);

        $I->amOnPage('/forum.topic.view/' . $topic->uuid->toString());
        $I->click($topic->user->username, '.post-author');
        $I->seePageIsOk('profile');
        $I->see($topic->user->username, '.profile-username');

        $I->amOnPage('/forum.index/');
        $I->click('.own-profile-link');
        $I->seePageIsOk('own profile');
        $I->see($user->username, '.profile-username');
        $I->seeInCurrentUrl($user->uuid->toString());
    }

    /**
     * Slice 4d over HTTP: the signature saved on Web:User:Edit (CSRF token of the form) is shown on the profile as Markdown.
     */
    public function theSignatureFromTheSettingsIsShownOnTheProfile(FunctionalTester $I) : void
    {
        $user = $I->createUser('signature');
        $I->login($user->username, FunctionalTester::PASSWORD);

        $I->amOnPage('/web.user.edit/');
        $I->seePageIsOk('settings');
        $I->submitForm('#frm-editForm', ['signature' => 'Signed *in italics*']);
        $I->seePageIsOk('settings saved');

        $I->amOnPage('/web.user.view/' . $user->uuid->toString());
        $I->seePageIsOk('profile');
        $I->seeInSource('<em>in italics</em>');
    }

    private function anyTopic(FunctionalTester $I) : TopicEntity
    {
        $topic = $I->grabService(EntityManagerDecorator::class)->getRepository(TopicEntity::class)->findOneBy([], ['id' => 'ASC']);
        $I->assertNotNull($topic, 'The fixtures have no topic.');
        $this->viewedTopic = [$topic->id, $topic->viewCount];

        return $topic;
    }

}
