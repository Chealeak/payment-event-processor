<?php

namespace App\Controller;

use App\Entity\PaymentEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PaymentEventController extends AbstractController
{
    #[Route('/api/payment-events', methods: ['POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid request data'], 400);
        }

        $paymentEvent = new PaymentEvent(
            $data['eventId'],
            $data['type'],
            $data['paymentId'],
            $data['payload'],
        );

        $em->persist($paymentEvent);
        $em->flush();

        return $this->json(['message' => 'Payment event created'], 201);
    }
}
