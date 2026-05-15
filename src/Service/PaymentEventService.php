<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PaymentEvent;
use App\Entity\OutboxEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class PaymentEventService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function ingest(
        Uuid $eventId,
        string $type,
        string $paymentId,
        array $payload,
    ): bool {
        $paymentEvent = new PaymentEvent(
            $eventId,
            $type,
            $paymentId,
            $payload,
        );

        $outboxEvent = new OutboxEvent(
            $eventId,
            $type,
            [
                'paymentId' => $paymentId,
                'payload' => $payload,
            ]
        );

        $this->em->wrapInTransaction(function () use ($paymentEvent, $outboxEvent) {
            $this->em->persist($paymentEvent);
            $this->em->persist($outboxEvent);
        });

        return true;
    }
}