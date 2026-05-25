<?php

declare(strict_types=1);

namespace App\Service;

use App\Message\PaymentEventMessage;
use App\Repository\OutboxEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

final class OutboxProcessor
{
    public function __construct(
        private readonly OutboxEventRepository $outboxEventRepository,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $em,
        #[Autowire(param: 'app.outbox.batch_size')]
        private readonly int $batchSize,
        #[Autowire(param: 'app.outbox.stale_after_seconds')]
        private readonly int $staleAfterSeconds,
    ) {}

    public function processBatch(): array
    {
        $recovered = $this->outboxEventRepository->resetStaleDispatched(
            new \DateTimeImmutable(sprintf('-%d seconds', $this->staleAfterSeconds)),
        );

        $dispatched = 0;

        $this->em->wrapInTransaction(function () use (&$dispatched): void {
            $events = $this->outboxEventRepository->findPendingDispatchWithLock($this->batchSize);

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
                ++$dispatched;
            }
        });

        return ['recovered' => $recovered, 'dispatched' => $dispatched];
    }
}
