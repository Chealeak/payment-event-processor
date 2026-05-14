<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Uid\Uuid;

class PaymentEventMessage
{
    public function __construct(
        public readonly Uuid $eventId,
        public readonly string $type,
        public readonly string $paymentId,
        public readonly array $payload,
    ) {}
}