<?php declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Entity\FaqEntity;
use App\Model\Entity\FaqTranslationEntity;
use App\Model\Entity\LanguageEntity;
use App\Model\Service\AdminException;
use App\Model\Service\FaqService;
use Tests\Support\IntegrationTester;

/**
 * The FAQ (slice 7e2): the administration (a question and an answer for each language) and the public page. Rolled back after
 * each test.
 */
class AdminFaqPagesCest
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

    public function theAdminAddsEditsAndDeletesAQuestion(IntegrationTester $I) : void
    {
        [$czech, $english] = $this->languages($I);
        $I->loginAsAdmin();

        $I->amOnPage('/admin.faq.list/');
        $I->click('.faq-create-link');
        $I->submitForm('#frm-faqForm', [
            'texts' => [
                $czech->id => ['question' => 'Jak napsat téma?', 'answer' => 'Tlačítkem **Nové téma**.'],
                $english->id => ['question' => 'How to write a topic?', 'answer' => 'By the button **New topic**.'],
            ],
            'sortOrder' => '5',
        ]);

        $text = $I->em()->getRepository(FaqTranslationEntity::class)->findOneBy(['question' => 'How to write a topic?']) ?? $I->fail('Not saved.');
        $faqId = $text->faq->id;
        $I->assertSame(5, $text->faq->sortOrder);
        $I->assertCount(2, $I->em()->getRepository(FaqTranslationEntity::class)->findBy(['faq' => $faqId]));

        $I->amOnPage('/admin.faq.edit/default/' . $faqId);
        $I->seeInField('#frm-faqForm input[name="texts[' . $czech->id . '][question]"]', 'Jak napsat téma?');
        $I->submitForm('#frm-faqForm', [
            'texts' => [
                $czech->id => ['question' => 'Jak založit téma?', 'answer' => 'Tlačítkem **Nové téma**.'],
                $english->id => ['question' => 'How to write a topic?', 'answer' => 'By the button **New topic**.'],
            ],
            'sortOrder' => '5',
        ]);
        $I->em()->clear();
        $I->assertNotNull($I->em()->getRepository(FaqTranslationEntity::class)->findOneBy(['question' => 'Jak založit téma?']));

        // the public page, the answer in Markdown
        $I->amOnPage('/forum.faq/');
        $I->see('Jak založit téma?', '.faq-question');
        $I->seeElement('.faq-answer strong');

        $I->amOnPage('/admin.faq.delete/default/' . $faqId);
        $I->submitForm('#frm-deleteForm', []);
        $I->assertNull($I->reload(FaqEntity::class, $faqId));
        $I->assertSame([], $I->em()->getRepository(FaqTranslationEntity::class)->findBy(['faq' => $faqId]));
    }

    public function aQuestionNeedsTheTextsInAllLanguages(IntegrationTester $I) : void
    {
        [$czech, $english] = $this->languages($I);
        $service = $I->grabService(FaqService::class);

        try {
            $service->save(null, 0, [$czech->id => ['question' => 'Otázka', 'answer' => 'Odpověď'], $english->id => ['question' => ' ', 'answer' => 'Answer']]);
            $I->fail('Saved without the English question.');
        } catch (AdminException $exception) {
            $I->assertSame(FaqService::ERROR_TEXT, $exception->getMessage());
            $I->assertSame(['language' => $english->name], $exception->parameters);
        }
    }

    public function theReaderGetsTheirLanguageOrTheDefaultOne(IntegrationTester $I) : void
    {
        [$czech, $english] = $this->languages($I);
        $service = $I->grabService(FaqService::class);
        $second = $service->save(null, 2, [$czech->id => ['question' => 'Druhá', 'answer' => 'B'], $english->id => ['question' => 'Second', 'answer' => 'B']]);
        $service->save(null, 1, [$czech->id => ['question' => 'První', 'answer' => 'A'], $english->id => ['question' => 'First', 'answer' => 'A']]);

        $questions = fn (string $locale) : array => array_map(fn (FaqTranslationEntity $t) : string => $t->question, $service->forLocale($locale));
        $I->assertSame(['První', 'Druhá'], $questions('cs'));
        $I->assertSame(['First', 'Second'], $questions('en'));

        // a text missing in the language (a language added later): the default language
        $I->em()->remove($service->texts($second)[$english->id]);
        $I->em()->flush();
        $I->assertSame(['First', 'Druhá'], $questions('en'));
    }

    public function theAdministrationIsOnlyForTheAdminThePageForEveryone(IntegrationTester $I) : void
    {
        $I->amOnPage('/forum.faq/');
        $I->seeResponseCodeIs(200);

        $user = $I->createUser('NotAdmin', role: 'Normal');
        $I->login($user->username, IntegrationTester::PASSWORD);

        $I->seePageIsRefused('/admin.faq.list/', 403);
        $I->seePageIsRefused('/admin.faq.create/', 403);
    }

    /**
     * @return array{LanguageEntity, LanguageEntity}
     */
    private function languages(IntegrationTester $I) : array
    {
        $repository = $I->em()->getRepository(LanguageEntity::class);

        return [
            $repository->findOneBy(['locale' => 'cs']) ?? $I->fail('No Czech.'),
            $repository->findOneBy(['locale' => 'en']) ?? $I->fail('No English.'),
        ];
    }
}
