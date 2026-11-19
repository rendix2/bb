<?php declare(strict_types=1);

namespace App\ConsoleModule\Scheduler;

use RuntimeException;

/**
 * A scheduled cron:* command ended with a non-zero exit code (CommandJob).
 */
final class CommandJobFailedException extends RuntimeException
{
}
