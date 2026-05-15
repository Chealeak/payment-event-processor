<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OutboxEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class OutboxEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OutboxEvent::class);
    }

    public function findUnprocessed(int $limit = 50): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.processed = false')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}