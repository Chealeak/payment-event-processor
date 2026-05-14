<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PaymentEvent;
use App\Message\PaymentEventMessage;
use App\Repository\PaymentEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;

class PaymentEventService
{
    public function __construct(
        private readonly PaymentEventRepository $paymentEventRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
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
        
        $this->entityManager->persist($paymentEvent);
        $this->entityManager->flush();

        $this->bus->dispatch(new PaymentEventMessage(
            $eventId,
            $type,
            $paymentId,
            $payload,
        ));

        return true;
    }
}