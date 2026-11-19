<?php declare(strict_types=1);

namespace Tests\Functional;

use App\Core\AutoLoginAuthenticator;
use App\Database\EntityManagerDecorator;
use App\Model\Entity\UserAutoLoginEntity;
use App\Model\Entity\UserEntity;
use Tests\Support\FunctionalTester;

/**
 * Login/logout, submitted the way the browser does it (ported from web-project 2026-11-19). Covers the bugs of web-project 2026-09-28,
 * which bb had too:
 * "stay logged in" not actually persisting (session-only cookie, unconditional 2h session
 * expiration, and the autologin cookie never being consulted outside the Admin module), logout
 * leaving the autologin cookie behind after removing its DB row, and the hardcoded/untranslated
 * "already logged in" message.
 */
class LoginLogoutCest
{

    public function stayingLoggedInPersistsAcrossNewBrowserSession(FunctionalTester $I) : void
    {
        $userEntity = $I->createUser('stay');

        $I->amOnPage('/web.user.login/');
        $I->checkOption('#frm-loginForm-stayLoggedIn');
        $I->submitForm('#frm-loginForm', ['username' => $userEntity->username, 'password' => FunctionalTester::PASSWORD]);
        $I->seeResponseCodeIs(200);
        $I->dontSeeInSource('id="tracy-bs"');
        $I->seeCookie(AutoLoginAuthenticator::COOKIE_NAME);

        /** @var EntityManagerDecorator $em */
        $em = $I->grabService(EntityManagerDecorator::class);
        $em->clear();
        $autoLoginCount = $em->getRepository(UserAutoLoginEntity::class)->count(['user' => $userEntity->id]);
        $I->assertSame(1, $autoLoginCount, 'Checking "stay logged in" did not create a UserAutoLoginEntity.');

        // Simulate closing and reopening the browser: the session cookie is gone, only the
        // persistent autoLogin cookie survives - exactly what AutoLoginService::tryLogin (wired
        // onto Application::onPresenter in config/common.neon) is meant to handle.
        $I->resetCookie('PHPSESSID');

        $I->amOnPage('/web.user.login/');
        $I->seeResponseCodeIs(200);
        $I->dontSeeInSource('id="tracy-bs"');
        $I->see('Již jste přihlášeni.');
    }

    public function loggingInWithoutStayLoggedInDoesNotCreateAnAutoLoginCookie(FunctionalTester $I) : void
    {
        $userEntity = $I->createUser('nostay');

        $I->amOnPage('/web.user.login/');
        $I->submitForm('#frm-loginForm', ['username' => $userEntity->username, 'password' => FunctionalTester::PASSWORD]);
        $I->seeResponseCodeIs(200);
        $I->dontSeeInSource('id="tracy-bs"');
        $I->dontSeeCookie(AutoLoginAuthenticator::COOKIE_NAME);

        $I->resetCookie('PHPSESSID');

        $I->amOnPage('/web.user.login/');
        $I->dontSee('Již jste přihlášeni.');
    }

    public function loggingOutRemovesTheAutoLoginCookieAndEntity(FunctionalTester $I) : void
    {
        $userEntity = $I->createUser('logout');

        $I->amOnPage('/web.user.login/');
        $I->checkOption('#frm-loginForm-stayLoggedIn');
        $I->submitForm('#frm-loginForm', ['username' => $userEntity->username, 'password' => FunctionalTester::PASSWORD]);
        $I->seeResponseCodeIs(200);
        $I->seeCookie(AutoLoginAuthenticator::COOKIE_NAME);

        $I->amOnPage('/web.user.logout/');
        $I->seeResponseCodeIs(200);
        $I->dontSeeInSource('id="tracy-bs"');
        $I->dontSeeCookie(AutoLoginAuthenticator::COOKIE_NAME);

        /** @var EntityManagerDecorator $em */
        $em = $I->grabService(EntityManagerDecorator::class);
        $em->clear();
        $autoLoginCount = $em->getRepository(UserAutoLoginEntity::class)->count(['user' => $userEntity->id]);
        $I->assertSame(0, $autoLoginCount, 'Logout did not remove the UserAutoLoginEntity.');

        // A stale/removed autologin cookie must never break a public page - AutoLoginService
        // swallows the resulting AuthenticationException and continues as not logged in.
        $I->resetCookie('PHPSESSID');
        $I->amOnPage('/web.user.login/');
        $I->seeResponseCodeIs(200);
        $I->dontSeeInSource('id="tracy-bs"');
        $I->dontSee('Již jste přihlášeni.');
    }

    public function loggingOutWhileAlreadyLoggedOutRedirectsInsteadOfErroring(FunctionalTester $I) : void
    {
        $I->amOnPage('/web.user.logout/');
        $I->seeResponseCodeIs(200);
        $I->dontSeeInSource('id="tracy-bs"');
        $I->seeCurrentUrlEquals('/web.user.login/');
    }

    /**
     * bb regression 2026-11-19: the identity of the "stay logged in" cookie had `name` instead of `username` and no
     * `user_last_login_time`, so the forum (its layout prints the username, its index reads the time) failed for such a visitor.
     */
    public function theForumWorksForVisitorLoggedInByTheCookie(FunctionalTester $I) : void
    {
        $userEntity = $I->createUser('cookieforum');

        $I->amOnPage('/web.user.login/');
        $I->checkOption('#frm-loginForm-stayLoggedIn');
        $I->submitForm('#frm-loginForm', ['username' => $userEntity->username, 'password' => FunctionalTester::PASSWORD]);
        $I->resetCookie('PHPSESSID');

        $I->amOnPage('/forum.index/');
        $I->seePageIsOk('the forum index of a visitor logged in by the cookie');
        $I->seeInSource($userEntity->username);
    }

    /**
     * bb regression 2026-11-19: a failed login is recorded with the country of the IP, GeoIpService answers `Local` for a private
     * address and the column had 2 characters - every wrong password ended in a 500 (and so did "stay logged in").
     */
    public function aWrongPasswordFromPrivateAddressAnswersTheForm(FunctionalTester $I) : void
    {
        $userEntity = $I->createUser('wrongpw');

        $I->amOnPage('/web.user.login/');
        $I->submitForm('#frm-loginForm', ['username' => $userEntity->username, 'password' => 'wrong-password']);
        $I->seePageIsOk('a wrong password');
        $I->seeElement('#frm-loginForm');
    }
}
