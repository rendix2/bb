<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\UserEntity;
use App\Model\Entity\UserFavouriteUserEntity;
use App\Model\Service\UserFavouriteException;
use App\Model\Service\UserFavouriteService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Tests\Support\IntegrationTester;

/**
 * The favourite users (slice 4f): added / removed on the profile by a POST form with a CSRF token (the old code did it by a GET
 * link), never the user himself, everyone only once (unique index), the list "My favourites" only for its owner (decision of
 * 2026-11-19: private). Every test runs in a transaction that is rolled back.
 */
class UserFavouritePagesCest
{
    public function _before(IntegrationTester $I) : void
    {
        $I->em()->getConnection()->beginTransaction();
    }

    public function _after(IntegrationTester $I) : void
    {
        if ($I->em()->getConnection()->isTransactionActive()) {
            $I->em()->getConnection()->rollBack();
        }

        $I->em()->clear();
    }

    /**
     * Regression: the old profile asked whether the user was HIS OWN favourite, so the button always offered "add". The button
     * follows whether the shown user is a favourite of the viewer.
     */
    public function aFavouriteIsAddedAndRemovedOnTheProfile(IntegrationTester $I) : void
    {
        $viewer = $I->createUser('Fan', role: 'Normal');
        $shown = $I->createUser('Star', role: 'Normal');
        $I->login($viewer->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.view/' . $shown->uuid->toString());
        $I->seeElement('.profile-favourite-form[data-favourite="no"]');
        $I->submitForm('#frm-favouriteForm', []);
        $I->assertSame(1, $this->count($I, $viewer, $shown));

        $I->amOnPage('/web.user.view/' . $shown->uuid->toString());
        $I->seeElement('.profile-favourite-form[data-favourite="yes"]');

        // the other way round he is not a favourite
        $I->login($shown->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.view/' . $viewer->uuid->toString());
        $I->seeElement('.profile-favourite-form[data-favourite="no"]');

        $I->login($viewer->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.view/' . $shown->uuid->toString());
        $I->submitForm('#frm-favouriteForm', []);
        $I->assertSame(0, $this->count($I, $viewer, $shown));
    }

    /**
     * Regression: the old code added a favourite by a GET link (?do=setFavourite), any page could do it for the user (CSRF).
     */
    public function aFavouriteIsNotAddedByGetNorWithoutTheToken(IntegrationTester $I) : void
    {
        $viewer = $I->createUser('Fan', role: 'Normal');
        $shown = $I->createUser('Star', role: 'Normal');
        $I->login($viewer->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.view/' . $shown->uuid->toString() . '?do=favouriteForm-submit');
        $I->seePageIsRefused('/web.user.view/' . $shown->uuid->toString() . '?do=setFavourite&user_id=' . $shown->id, 403);
        $I->assertSame(0, $this->count($I, $viewer, $shown));

        $I->amOnPage('/web.user.view/' . $shown->uuid->toString());
        $I->submitForm('#frm-favouriteForm', ['_token_' => 'forged']);
        $I->assertSame(0, $this->count($I, $viewer, $shown));
    }

    public function theUserIsNotHisOwnFavourite(IntegrationTester $I) : void
    {
        $user = $I->createUser('Self', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.view/');
        $I->dontSeeElement('#frm-favouriteForm');
        $I->seeElement('.profile-favourites-link');

        $I->expectThrowable(
            new UserFavouriteException(UserFavouriteService::ERROR_SELF),
            fn () => $I->grabService(UserFavouriteService::class)->add($user, $user),
        );
        $I->assertSame(0, $this->count($I, $user, $user));
    }

    public function aFavouriteIsThereOnlyOnce(IntegrationTester $I) : void
    {
        $viewer = $I->createUser('Fan', role: 'Normal');
        $shown = $I->createUser('Star', role: 'Normal');
        $service = $I->grabService(UserFavouriteService::class);

        $service->add($viewer, $shown);
        $service->add($viewer, $shown);
        $I->assertSame(1, $this->count($I, $viewer, $shown));

        // also the database refuses a second row (unique index user_id + favourite_user_id)
        $I->expectThrowable(UniqueConstraintViolationException::class, function () use ($I, $viewer, $shown) : void {
            $I->em()->getConnection()->insert('user_favourite', [
                'uuid' => '00000000-0000-4000-8000-000000000001',
                'user_id' => $viewer->id,
                'favourite_user_id' => $shown->id,
                'created_at' => '2026-11-23 10:00:00',
            ]);
        });
    }

    /**
     * Decision of 2026-11-19: the favourites are private. The page has no uuid of a user: with one in the address it still shows
     * the own list; a guest gets the login.
     */
    public function myFavouritesSeesOnlyTheOwner(IntegrationTester $I) : void
    {
        $owner = $I->createUser('Owner', role: 'Normal');
        $star = $I->createUser('Star', role: 'Normal');
        $other = $I->createUser('Other', role: 'Normal');
        $secret = $I->createUser('Secret', role: 'Normal');
        $service = $I->grabService(UserFavouriteService::class);
        $service->add($owner, $star);
        $service->add($other, $secret);

        $I->amOnPage('/web.user.favourites/');
        $I->seeElement('#frm-loginForm');

        $I->login($owner->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.view/');
        $I->click('.profile-favourites-link');
        $I->seeResponseCodeIs(200);
        $I->see($star->username, '.user-favourite-name');
        $I->dontSee($secret->username);

        $I->amOnPage('/web.user.favourites/' . $other->uuid->toString());
        $I->see($star->username, '.user-favourite-name');
        $I->dontSee($secret->username);

        // the other user does not see the list of the owner
        $I->login($other->username, IntegrationTester::PASSWORD);
        $I->amOnPage('/web.user.favourites/' . $owner->uuid->toString());
        $I->see($secret->username, '.user-favourite-name');
        $I->dontSee($star->username);
        $I->amOnPage('/web.user.view/' . $owner->uuid->toString());
        $I->dontSeeElement('.profile-favourites-link');
    }

    public function aFavouriteIsRemovedFromTheList(IntegrationTester $I) : void
    {
        $owner = $I->createUser('Owner', role: 'Normal');
        $star = $I->createUser('Star', role: 'Normal');
        $I->grabService(UserFavouriteService::class)->add($owner, $star);
        $I->login($owner->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/web.user.favourites/');
        $I->submitForm('#frm-removeForm-' . $star->id, ['_token_' => 'forged']);
        $I->assertSame(1, $this->count($I, $owner, $star));

        $I->amOnPage('/web.user.favourites/');
        $I->submitForm('#frm-removeForm-' . $star->id, []);
        $I->assertSame(0, $this->count($I, $owner, $star));
    }

    private function count(IntegrationTester $I, UserEntity $user, UserEntity $favourite) : int
    {
        return $I->em()->getRepository(UserFavouriteUserEntity::class)->count(['user' => $user, 'favouriteUser' => $favourite]);
    }
}
