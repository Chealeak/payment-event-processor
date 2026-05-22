<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OutboxEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

final class OutboxEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OutboxEvent::class);
    }

    public function findPendingDispatch(int $limit = 50): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.processed = false')
            ->andWhere('o.dispatchedAt IS NULL')
            ->orderBy('o.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOneByEventId(Uuid $eventId): ?OutboxEvent
    {
        return $this->findOneBy(['eventId' => $eventId]);
    }
}