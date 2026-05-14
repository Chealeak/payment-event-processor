<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PaymentEventMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PaymentEventHandler
{
    public function __invoke(PaymentEventMessage $message): void
    {
        // TODO: Implement the logic to handle the payment event

        file_put_contents(
            'var/log/payment_events.log',
            json_encode([
                'eventId' => (string) $message->eventId,
                'type' => $message->type,
            ]) . PHP_EOL,
            FILE_APPEND
        );
    }
}