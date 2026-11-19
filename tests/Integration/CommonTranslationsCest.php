<?php declare(strict_types=1);

namespace Tests\Integration;

use Nette\Localization\Translator;
use Tests\Support\IntegrationTester;

/**
 * bb regression 2026-11-19: the pages use the shared keys `messages.*` (operationFailed, yes, no, select), but bb had no
 * `messages.*.json`, so the user saw the bare key. The file is app/Core/Translations/messages.{cs,en}.json (as in web-project).
 */
class CommonTranslationsCest
{
    public function theSharedMessagesAreTranslated(IntegrationTester $I) : void
    {
        $translator = $I->grabService(Translator::class);

        foreach (['operationFailed', 'yes', 'no', 'select'] as $key) {
            $text = (string) $translator->translate('messages.' . $key);

            $I->assertNotSame('messages.' . $key, $text, 'messages.' . $key . ' is not translated.');
        }

        $I->assertSame('Operace se nezdařila.', (string) $translator->translate('messages.operationFailed'));
    }
}
