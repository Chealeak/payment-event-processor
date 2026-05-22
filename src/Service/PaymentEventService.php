<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OutboxEvent;
use App\Entity\PaymentEvent;
use App\Repository\PaymentEventRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class PaymentEventService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PaymentEventRepository $paymentEventRepository,
    ) {}

    public function ingest(
        Uuid $eventId,
        string $type,
        string $paymentId,
        array $payload,
    ): bool {
        if ($this->paymentEventRepository->existsByEventId($eventId)) {
            return false;
        }

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

        try {
            $this->em->wrapInTransaction(function () use ($paymentEvent, $outboxEvent): void {
                $this->em->persist($paymentEvent);
                $this->em->persist($outboxEvent);
            });
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        return true;
    }
}
