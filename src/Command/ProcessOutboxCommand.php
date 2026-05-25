<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\OutboxProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:process-outbox',
    description: 'Dispatch pending outbox events to the message bus',
)]
class ProcessOutboxCommand extends Command
{
    public function __construct(
        private readonly OutboxProcessor $outboxProcessor,
        #[Autowire(param: 'app.outbox.poll_interval_seconds')]
        private readonly int $pollIntervalSeconds,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'watch',
            null,
            InputOption::VALUE_NONE,
            'Run continuously, polling every OUTBOX_POLL_INTERVAL_SECONDS',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        do {
            $result = $this->outboxProcessor->processBatch();

            $output->writeln(sprintf(
                'Outbox: recovered %d stale, dispatched %d',
                $result['recovered'],
                $result['dispatched'],
            ));

            if (!$input->getOption('watch')) {
                break;
            }

            if ($this->pollIntervalSeconds > 0) {
                sleep($this->pollIntervalSeconds);
            }
        } while (true);

        return Command::SUCCESS;
    }
}
