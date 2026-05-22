<?php

namespace App\Controller;

use App\Service\PaymentEventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class PaymentEventController extends AbstractController
{
    #[Route('/api/payment-events', methods: ['POST'])]
    public function __invoke(
        Request $request,
        PaymentEventService $paymentEventService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid request data'], 400);
        }
        
        foreach (['eventId', 'type', 'paymentId', 'payload'] as $field) {
            if (!isset($data[$field])) {
                return $this->json([
                    'message' => sprintf('Missing field: %s', $field)
                ], 400);
            }
        }

        $created = $paymentEventService->ingest(
            Uuid::fromString($data['eventId']),
            $data['type'],
            $data['paymentId'],
            $data['payload'],
        );

        if (!$created) {
            return $this->json(['message' => 'Event already exists'], 409);
        }

        return $this->json(['message' => 'Event created'], 201);
    }
}
