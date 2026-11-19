<?php declare(strict_types=1);

namespace App\ConsoleModule\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\InvalidConfigurationException;
use Override;

/**
 * Adds the services tagged `scheduler.job: <key>` to the scheduler of contributte/scheduler under that key.
 *
 * The extension of contributte/scheduler takes a job only as a string (a class it creates itself) or an array (a callback
 * + cron, or a class without arguments), never a service with arguments such as CommandJob('0 10 * * *', @command, 59), and a
 * string '@service' is not resolved as a reference. So the jobs are ordinary services (config/scheduler.neon) and this
 * extension hands them to the scheduler - `scheduler:list`, `scheduler:run` and `scheduler:force-run <key>` see them.
 */
final class SchedulerJobsExtension extends CompilerExtension
{
    public const string TAG = 'scheduler.job';

    private const string SCHEDULER = 'scheduler.scheduler';

    #[Override]
    public function beforeCompile() : void
    {
        $builder = $this->getContainerBuilder();
        $scheduler = $builder->getDefinition(self::SCHEDULER);

        if (!$scheduler instanceof ServiceDefinition) {
            throw new InvalidConfigurationException('The scheduler of contributte/scheduler (' . self::SCHEDULER . ') is missing.');
        }

        foreach ($builder->findByTag(self::TAG) as $service => $key) {
            if (!is_string($key) || $key === '') {
                throw new InvalidConfigurationException('The service ' . $service . ' needs the key of its job: tags: [' . self::TAG . ': <key>].');
            }

            $scheduler->addSetup('add', [new Reference($service), $key]);
        }
    }
}
