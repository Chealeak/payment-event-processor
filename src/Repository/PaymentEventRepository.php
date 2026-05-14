<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PaymentEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

final class PaymentEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentEvent::class);
    }

    public function existsByEventId(Uuid $eventId): bool
    {
        return $this->findOneBy(['eventId' => $eventId]) !== null;
    }
}