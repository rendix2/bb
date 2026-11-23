<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\LanguageEntity;
use App\Model\Entity\UserEntity;
use Contributte\Translation\Translator;
use Tests\Support\IntegrationTester;

/**
 * The language of the interface (decision of 2026-11-20): a logged-in user sees it in the language of his settings
 * (UserLocaleResolver: language.locale cs/en), a guest in the default one (cs). Before, the setting was saved
 * but the interface stayed Czech. Every test runs in a transaction that is rolled back.
 */
class UserInterfaceLanguageCest
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

    public function aGuestSeesCzech(IntegrationTester $I) : void
    {
        $I->amOnPage('/forum.index/');
        $I->seeResponseCodeIs(200);
        $I->see('Přihlásit');
        $I->dontSee('Log in');

        $I->amOnPage('/web.user.login/');
        $I->see('Uživatelské jméno');
    }

    /**
     * Also the answer of the login form itself (the same request) is already English.
     */
    public function aUserWithEnglishSeesEnglish(IntegrationTester $I) : void
    {
        $user = $this->createUserWithLanguage($I, 'English', 'english');

        $I->amOnPage('/web.user.login/');
        $I->submitForm('#frm-loginForm', ['username' => $user->username, 'password' => IntegrationTester::PASSWORD]);
        $I->see('Successfully logged in');

        $I->amOnPage('/forum.index/');
        $I->see('Log out');
        $I->dontSee('Odhlásit');

        $I->amOnPage('/web.user.view/');
        $I->see('Registered');
        $I->dontSee('Registrace');
    }

    /**
     * The locale comes from language.locale, not from the name of the language: an English language under another
     * file name is still English (before, a hard-coded map file_name => locale; a new language had no locale).
     */
    public function theLocaleIsTheColumnOfTheLanguage(IntegrationTester $I) : void
    {
        $user = $this->createUserWithLanguage($I, 'Renamed', 'english');
        $user->language->fileName = 'anglictina';
        $user->language->name = 'Angličtina';
        $I->em()->flush();

        $I->amOnPage('/web.user.login/');
        $I->submitForm('#frm-loginForm', ['username' => $user->username, 'password' => IntegrationTester::PASSWORD]);
        $I->see('Successfully logged in');
        $I->amOnPage('/forum.index/');
        $I->see('Log out');
    }

    public function aUserWithCzechSeesCzech(IntegrationTester $I) : void
    {
        $user = $this->createUserWithLanguage($I, 'Czech', 'czech');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->amOnPage('/forum.index/');
        $I->see('Odhlásit');
        $I->dontSee('Log out');

        $I->amOnPage('/web.user.view/');
        $I->see('Registrace');
    }

    /**
     * The language is read from the database, not from the session: a changed setting applies at the next page; after the logout
     * the interface is the default one again.
     */
    public function aChangedLanguageAppliesAtTheNextPageAndTheLogoutGoesBack(IntegrationTester $I) : void
    {
        $user = $this->createUserWithLanguage($I, 'Switch', 'czech');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $user->language = $this->language($I, 'english');
        $I->em()->flush();

        $I->amOnPage('/forum.index/');
        $I->see('Log out');

        $I->click('Log out');
        $I->amOnPage('/forum.index/');
        $I->see('Přihlásit');
    }

    public function theResolverMapsTheLanguagesOfTheFixtures(IntegrationTester $I) : void
    {
        foreach ($I->em()->getRepository(LanguageEntity::class)->findAll() as $language) {
            $I->assertContains($language->locale, $I->grabService(Translator::class)->getLocalesWhitelist() ?? [], 'The locale of the language ' . $language->name . ' is not allowed.');
            $I->assertFileExists(dirname(__DIR__, 2) . '/app/UI/Web/User/Edit/Translations/web-user-edit.' . $language->locale . '.json', 'The language ' . $language->name . ' has no translations.');
        }

        $I->assertSame('cs', $I->grabService(Translator::class)->getDefaultLocale());
    }

    private function createUserWithLanguage(IntegrationTester $I, string $label, string $fileName) : UserEntity
    {
        $user = $I->createUser($label, role: 'Normal');
        $user->language = $this->language($I, $fileName);
        $I->em()->flush();

        return $user;
    }

    private function language(IntegrationTester $I, string $fileName) : LanguageEntity
    {
        $language = $I->em()->getRepository(LanguageEntity::class)->findOneBy(['fileName' => $fileName]);
        $I->assertNotNull($language, 'The fixtures have no language ' . $fileName . '.');

        return $language;
    }
}
