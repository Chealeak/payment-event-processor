<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\PaymentEventMessage;
use App\Repository\OutboxEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:process-outbox',
    description: 'Dispatch pending outbox events to the message bus',
)]
class ProcessOutboxCommand extends Command
{
    public function __construct(
        private readonly OutboxEventRepository $repo,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->repo->findPendingDispatch(50);

        foreach ($events as $event) {
            $this->bus->dispatch(
                new PaymentEventMessage(
                    $event->getEventId(),
                    $event->getType(),
                    $event->getPayload()['paymentId'],
                    $event->getPayload()['payload'],
                )
            );

            $event->markDispatched();
            $this->em->flush();
        }

        return Command::SUCCESS;
    }
}
