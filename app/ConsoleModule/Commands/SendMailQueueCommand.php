<?php declare(strict_types=1);

namespace App\ConsoleModule\Commands;

use App\Model\Service\MailQueueService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends the queued mails (cron: php bin/console mail:send-queue).
 */
#[AsCommand(name: 'mail:send-queue', description: 'Sends the queued mails (the table mail, sent_at null).')]
class SendMailQueueCommand extends Command
{
    public function __construct(
        private readonly MailQueueService $mailQueue,
    ) {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'At most this many mails in one run.', '100');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $sent = $this->mailQueue->sendQueued(max(1, (int) $input->getOption('limit')));
        $output->writeln('Sent ' . $sent . ' mail(s).');

        return Command::SUCCESS;
    }
}
