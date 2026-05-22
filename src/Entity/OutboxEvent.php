<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OutboxEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OutboxEventRepository::class)]
#[ORM\Table(name: 'outbox_events')]
class OutboxEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $eventId;

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column]
    private bool $processed = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $dispatchedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Uuid $eventId,
        string $type,
        array $payload,
    ) {
        $this->eventId = $eventId;
        $this->type = $type;
        $this->payload = $payload;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventId(): Uuid
    {
        return $this->eventId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
    
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDispatchedAt(): ?\DateTimeImmutable
    {
        return $this->dispatchedAt;
    }

    public function markDispatched(): void
    {
        $this->dispatchedAt = new \DateTimeImmutable();
    }

    public function markProcessed(): void
    {
        $this->processed = true;
    }
}