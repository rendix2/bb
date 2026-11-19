<?php declare(strict_types=1);

namespace Tests\Unit;

use App\ConsoleModule\Scheduler\CommandJob;
use Codeception\Attribute\Group;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Tests\Support\UnitTester;

/**
 * The spread of a CommandJob (taken over from alfred): a job with a spread runs once a day at a pseudo-random minute of its
 * window (not at the same minute every day), and every per-minute `scheduler:run` of the day agrees on that minute.
 */
#[Group('cron')]
class CommandJobCest
{
    private const string PRAGUE = 'Europe/Prague';

    public function aJobWithoutSpreadIsDueExactlyOnItsExpression(UnitTester $I) : void
    {
        $job = new CommandJob('0 10 * * *', new Command('test:command'));

        $I->assertSame(0, $job->offsetMinutes($this->at('2026-11-14 10:00')));
        $I->assertTrue($job->isDue($this->at('2026-11-14 10:00')));
        $I->assertFalse($job->isDue($this->at('2026-11-14 10:01')));
    }

    public function aSpreadJobIsDueExactlyOnceDayInsideItsWindow(UnitTester $I) : void
    {
        $job = new CommandJob('0 10 * * *', new Command('mail:send-queue'), 59);

        foreach (['2026-11-14', '2026-11-15', '2026-07-01', '2027-03-28'] as $day) { // 2027-03-28 = the switch to summer time
            $dueMinutes = [];
            for ($minute = 0; $minute < 24 * 60; $minute++) {
                $time = sprintf('%s %02d:%02d', $day, intdiv($minute, 60), $minute % 60);
                if ($job->isDue($this->at($time))) {
                    $dueMinutes[] = $time;
                }
            }

            $I->assertCount(1, $dueMinutes, $day . ' runs exactly once: ' . implode(', ', $dueMinutes));
            $I->assertStringStartsWith($day . ' 10:', $dueMinutes[0], $day . ' runs between 10:00 and 10:59');
        }
    }

    public function theMinuteDiffersBetweenDays(UnitTester $I) : void
    {
        $job = new CommandJob('0 10 * * *', new Command('mail:send-queue'), 59);

        $offsets = [];
        foreach (range(1, 30) as $day) {
            $offsets[] = $job->offsetMinutes($this->at(sprintf('2026-11-%02d 10:00', $day)));
        }

        $I->assertGreaterThan(10, count(array_unique($offsets)), 'not the same minute every day');
        $I->assertLessThanOrEqual(59, max($offsets));
        $I->assertGreaterThanOrEqual(0, min($offsets));
    }

    public function aNegativeSpreadIsRejected(UnitTester $I) : void
    {
        $I->expectThrowable(InvalidArgumentException::class, static function () : void {
            new CommandJob('0 10 * * *', new Command('test:command'), -1);
        });
    }

    private function at(string $time) : DateTime
    {
        return new DateTime($time, new DateTimeZone(self::PRAGUE));
    }
}
