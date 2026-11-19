<?php declare(strict_types=1);

namespace Tests\Unit;

use Codeception\Attribute\Group;
use Tests\Support\UnitTester;

/**
 * A flash message is never a bare string: `flashMessage()` gets a \stdClass that has `place` (where on the page the message belongs,
 * `general` = the page), `type` (success / danger / info / warning) and `message`, all set by hand:
 *
 *     $flash = new stdClass();
 *     $flash->place = 'general';
 *     $flash->type = 'success';
 *     $flash->message = $this->translator->translate('...');
 *
 *     $this->flashMessage($flash);
 *
 * Nette uses the object as the flash itself, so the templates keep reading `$flash->type` and `$flash->message`.
 *
 * bb (ported from web-project 2026-11-19): the account pages (app/UI/Web) are guarded; the rest of the application (the forum of
 * the slices 1-3, the admin grids, the old code) still passes strings, the whole scan is a known bug until it is converted.
 */
class FlashMessageIsAnObjectCest
{
    public function theAccountPagesGiveAnObjectWithPlaceTypeAndMessage(UnitTester $I) : void
    {
        $this->scan($I, dirname(__DIR__, 2) . '/app/UI/Web', 5);
    }

    #[Group('known-bug')]
    public function flashMessageGetsAnObjectWithPlaceTypeAndMessage(UnitTester $I) : void
    {
        $this->scan($I, dirname(__DIR__, 2) . '/app', 20);
    }

    private function scan(UnitTester $I, string $root, int $minimum) : void
    {
        $violations = [];
        $calls = 0;

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $content = (string) file_get_contents($file->getPathname());
            $name = substr($file->getPathname(), strlen($root) + 1);

            $total = (int) preg_match_all('~->flashMessage\(~', $content, $all);
            $withObject = (int) preg_match_all('~->flashMessage\(\s*\$flash\w*\s*\)~', $content, $objects);

            if ($total === 0) {
                continue;
            }

            $calls += $total;

            // a string (or anything but the $flash variable) as the argument
            if ($withObject !== $total) {
                $violations[] = $name . ': flashMessage() with ' . ($total - $withObject) . ' argument(s) that are not a $flash object';
            }

            // the text of an exception is for the log, not for the user (and it is not translated): translate the cause
            if (preg_match('~\$flash\w*->message\s*=\s*\$\w+->getMessage\(\)~', $content) === 1) {
                $violations[] = $name . ': the message of a flash is the raw message of an exception';
            }

            // every object is built field by field: a place, a type and a message per call
            foreach (['place', 'type', 'message'] as $field) {
                $assigned = preg_match_all('~\$flash\w*->' . $field . '\s*=~', $content);

                if ($assigned < $withObject) {
                    $violations[] = $name . ': ' . $withObject . ' flashMessage($flash) but only ' . $assigned . ' x $flash->' . $field . ' =';
                }
            }
        }

        $I->assertGreaterThan($minimum, $calls, 'The scan did not find the flash messages.');
        $I->assertSame([], $violations, "Build the flash as a stdClass (place, type, message):\n" . implode("\n", $violations));
    }
}
