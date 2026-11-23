<?php declare(strict_types=1);

namespace Tests\Functional;

use App\Database\EntityManagerDecorator;
use App\Model\Entity\LanguageEntity;
use Tests\Support\FunctionalTester;

/**
 * The language of the interface over HTTP (every request a new container of the web server): a user with English sees the forum
 * in English, a guest in Czech.
 */
class UserInterfaceLanguageCest
{
    public function aUserWithEnglishSeesEnglishAndAGuestCzech(FunctionalTester $I) : void
    {
        $I->amOnPage('/forum.index/');
        $I->seePageIsOk('index of a guest');
        // see() over the large index breaks on the PCRE limit (gotchas): the source
        $I->seeInSource('>Přihlásit</a>');

        $em = $I->grabService(EntityManagerDecorator::class);
        $english = $em->getRepository(LanguageEntity::class)->findOneBy(['fileName' => 'english']);
        $I->assertNotNull($english);

        $user = $I->createUser('English');
        $user->language = $english;
        $em->flush();

        $I->login($user->username, FunctionalTester::PASSWORD);
        $I->amOnPage('/forum.index/');
        $I->seePageIsOk('index of the user');
        $I->seeInSource('>Log out</a>');
        $I->dontSeeInSource('>Odhlásit</a>');
    }
}
