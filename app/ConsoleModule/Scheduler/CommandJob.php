<?php declare(strict_types=1);

namespace App\ConsoleModule\Scheduler;

use Contributte\Scheduler\ExpressionJob;
use DateTime;
use InvalidArgumentException;
use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * A contributte/scheduler job that runs one console command (cron:*) on a cron expression - the scheduled run and a manual
 * `php bin/console cron:...` do exactly the same thing.
 *
 * The expression is evaluated by dragonmantank/cron-expression in the time zone of the DateTime the scheduler passes in,
 * i.e. the default PHP time zone (php: date.timezone: Europe/Prague in config/common.neon).
 *
 * $spreadMinutes > 0 moves the run by a pseudo-random number of minutes (0..$spreadMinutes) after the expression, a different
 * one every day, so a message does not come at the same minute every day (it would look machine-made). The offset is derived
 * from the date and the command name, so every per-minute `scheduler:run` of that day agrees on it and the job runs once.
 * The spread window must not cross midnight.
 *
 * It is an ExpressionJob, so `scheduler:list` shows the expression (not "Dynamic"). contributte/scheduler accepts only a
 * string or an array as a job of its config, so the jobs are services (config/scheduler.neon) referenced by `@name`.
 * The scheduler catches an exception of a job (logs it) and goes on with the other jobs.
 *
 * Every run is logged to log/<context>/scheduler.log (the command's output); a failing run (exit code other than 0) is logged
 * as an error (log/<context>/error.log, + the configured loggers) and reported to the scheduler by an exception.
 */
final class CommandJob extends ExpressionJob
{
    public const string LOG = 'scheduler';

    public function __construct(
        string $cron,
        private readonly Command $command,
        private readonly int $spreadMinutes = 0,
    )
    {
        if ($spreadMinutes < 0) {
            throw new InvalidArgumentException('The spread of a job must not be negative, ' . $spreadMinutes . ' given.');
        }

        parent::__construct($cron);
    }

    #[Override]
    public function isDue(DateTime $dateTime) : bool
    {
        $offset = $this->offsetMinutes($dateTime);
        $shifted = (clone $dateTime)->modify('-' . $offset . ' minutes');

        return $this->expression->isDue($shifted);
    }

    /**
     * The pseudo-random delay of the run on the day of $dateTime, 0..spreadMinutes.
     */
    public function offsetMinutes(DateTime $dateTime) : int
    {
        if ($this->spreadMinutes === 0) {
            return 0;
        }

        return crc32($dateTime->format('Y-m-d') . '|' . $this->command->getName()) % ($this->spreadMinutes + 1);
    }

    public function run() : void
    {
        $output = new BufferedOutput();
        $exitCode = $this->command->run(new ArrayInput([]), $output);
        $name = (string) $this->command->getName();
        $text = trim($output->fetch());

        if ($exitCode !== Command::SUCCESS) {
            $message = sprintf('%s skončil s kódem %d: %s', $name, $exitCode, $text);
            Debugger::log($message, ILogger::ERROR);

            throw new CommandJobFailedException($message);
        }

        Debugger::log(sprintf('%s OK: %s', $name, $text), self::LOG);
    }

    public function getSpreadMinutes() : int
    {
        return $this->spreadMinutes;
    }

    public function getCommand() : Command
    {
        return $this->command;
    }
}
