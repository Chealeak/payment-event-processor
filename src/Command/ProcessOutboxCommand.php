<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\OutboxEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\PaymentEventMessage;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:process-outbox',
    description: 'Process outbox events',
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
        $events = $this->repo->findUnprocessed(50);
    
        foreach ($events as $event) {
            $this->bus->dispatch(
                new PaymentEventMessage(
                    $event->getEventId(),
                    $event->getType(),
                    $event->getPayload()['paymentId'],
                    $event->getPayload()['payload'],
                )
            );
    
            $event->markProcessed();
        }
    
        $this->em->flush();
    
        return Command::SUCCESS;
    }
}
