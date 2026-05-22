<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PaymentEventMessage;
use App\Repository\OutboxEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PaymentEventHandler
{
    public function __construct(
        private readonly OutboxEventRepository $outboxEventRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    public function __invoke(PaymentEventMessage $message): void
    {
        $outbox = $this->outboxEventRepository->findOneByEventId($message->eventId);

        if ($outbox === null || $outbox->isProcessed()) {
            return;
        }

        // TODO: handle the event

        $outbox->markProcessed();
        $this->em->flush();
    }
}
