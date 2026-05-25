<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\OutboxEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

final class OutboxEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OutboxEvent::class);
    }

    public function findPendingDispatchWithLock(int $limit): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $ids = $connection->fetchFirstColumn(
            'SELECT id FROM outbox_events
             WHERE processed = false AND dispatched_at IS NULL
             ORDER BY created_at ASC
             LIMIT :limit
             FOR UPDATE SKIP LOCKED',
            ['limit' => $limit],
            ['limit' => ParameterType::INTEGER],
        );

        if ($ids === []) {
            return [];
        }

        return $this->createQueryBuilder('o')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function resetStaleDispatched(\DateTimeImmutable $dispatchedBefore): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->update(OutboxEvent::class, 'o')
            ->set('o.dispatchedAt', ':null')
            ->where('o.processed = false')
            ->andWhere('o.dispatchedAt IS NOT NULL')
            ->andWhere('o.dispatchedAt < :before')
            ->setParameter('null', null)
            ->setParameter('before', $dispatchedBefore)
            ->getQuery()
            ->execute();
    }

    public function findOneByEventId(Uuid $eventId): ?OutboxEvent
    {
        return $this->findOneBy(['eventId' => $eventId]);
    }
}
