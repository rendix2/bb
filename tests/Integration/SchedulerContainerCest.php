<?php declare(strict_types=1);

namespace Tests\Integration;

use App\ConsoleModule\Scheduler\CommandJob;
use Contributte\Scheduler\IScheduler;
use Tests\Support\IntegrationTester;

/**
 * The crons of bb (config/scheduler.neon): the scheduler of the container has exactly the job of the mail queue, every
 * minute.
 */
class SchedulerContainerCest
{
    public function theSchedulerSendsTheMailQueueEveryMinute(IntegrationTester $I) : void
    {
        $scheduler = $I->grabService(IScheduler::class);
        $jobs = $scheduler->getAll();

        $I->assertSame(['mailQueue'], array_keys($jobs));
        $job = $scheduler->get('mailQueue');
        $I->assertInstanceOf(CommandJob::class, $job);
        $I->assertSame('mail:send-queue', $job->getCommand()->getName());
        $I->assertSame('* * * * *', $job->getExpression()->getExpression());
    }
}
