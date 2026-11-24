<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\LanguageEntity;
use App\Model\Entity\UserEntity;
use App\Model\Service\AdminException;
use App\Model\Service\LanguageAdminService;
use Tests\Support\IntegrationTester;

/**
 * The languages of the interface (slice 7d): added by locale only with complete translations, never renamed, the used and the
 * default one never deleted. Every test runs in a transaction that is rolled back.
 */
class AdminLanguagePagesCest
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
     * The rule: every language of the database has all the translations (to every *.cs.json its *.<locale>.json).
     */
    public function everyLanguageHasAllTheTranslations(IntegrationTester $I) : void
    {
        $service = $I->grabService(LanguageAdminService::class);

        foreach ($I->em()->getRepository(LanguageEntity::class)->findAll() as $language) {
            $I->assertSame([], $service->missingTranslations($language->locale), "The language {$language->locale} misses translations.");
        }
    }

    public function theAdminAddsALanguageWithCompleteTranslations(IntegrationTester $I) : void
    {
        // the English language is removed first (its users get Czech), so it can be added again
        $czech = $I->em()->getRepository(LanguageEntity::class)->findOneBy(['locale' => 'cs']) ?? $I->fail('No Czech.');
        $english = $I->em()->getRepository(LanguageEntity::class)->findOneBy(['locale' => 'en']) ?? $I->fail('No English.');
        $I->em()->createQueryBuilder()->update(UserEntity::class, '_u')->set('_u.language', ':cs')->where('_u.language = :en')
            ->setParameter('cs', $czech)->setParameter('en', $english)->getQuery()->execute();
        $I->em()->remove($english);
        $I->em()->flush();
        $I->loginAsAdmin();

        $I->amOnPage('/admin.language.list/');
        $I->dontSeeElement('#frm-addForm option[value="sk"]');
        $I->see('Slovenčina', '.language-incomplete');
        $I->submitForm('#frm-addForm', ['locale' => 'en']);

        $added = $I->em()->getRepository(LanguageEntity::class)->findOneBy(['locale' => 'en']);
        $I->assertSame('English', $added?->name);
        $I->assertSame('english', $added->fileName);
        $I->see('English', '.language');
    }

    public function onlyAnAllowedCompleteLanguageIsAdded(IntegrationTester $I) : void
    {
        $service = $I->grabService(LanguageAdminService::class);

        foreach (['sk', 'en', 'xx'] as $locale) {
            try {
                $service->add($locale);
                $I->fail("The language $locale was added.");
            } catch (AdminException $exception) {
                $I->assertSame(LanguageAdminService::ERROR_NOT_AVAILABLE, $exception->getMessage());
            }
        }
    }

    public function aUsedOrTheDefaultLanguageIsNotDeleted(IntegrationTester $I) : void
    {
        $czech = $I->em()->getRepository(LanguageEntity::class)->findOneBy(['locale' => 'cs']) ?? $I->fail('No Czech.');
        $english = $I->em()->getRepository(LanguageEntity::class)->findOneBy(['locale' => 'en']) ?? $I->fail('No English.');
        $user = $I->createUser('English', role: 'Normal');
        $user->language = $english;
        $I->em()->flush();
        $englishId = $english->id;
        $I->loginAsAdmin();

        $I->amOnPage('/admin.language.delete/default/' . $czech->id);
        $I->submitForm('#frm-deleteForm', []);
        $I->see('Výchozí jazyk webu nejde smazat');

        $I->amOnPage('/admin.language.delete/default/' . $englishId);
        $I->submitForm('#frm-deleteForm', []);
        $I->see('nejde smazat');

        $I->em()->createQueryBuilder()->update(UserEntity::class, '_u')->set('_u.language', ':cs')->where('_u.language = :en')
            ->setParameter('cs', $czech)->setParameter('en', $englishId)->getQuery()->execute();
        $I->amOnPage('/admin.language.delete/default/' . $englishId);
        $I->submitForm('#frm-deleteForm', []);
        $I->assertNull($I->reload(LanguageEntity::class, $englishId));
    }

    public function theLanguagesAreOnlyForTheAdmin(IntegrationTester $I) : void
    {
        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->seePageIsRefused('/admin.language.list/', 403);
    }
}
