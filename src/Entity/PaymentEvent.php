<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PaymentEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PaymentEventRepository::class)]
#[ORM\Table(name: 'payment_events')]
class PaymentEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $eventId;

    #[ORM\Column(length: 255)]
    private string $type;

    #[ORM\Column(length: 255)]
    private string $paymentId;

    #[ORM\Column(type: Types::JSON)]
    private array $payload;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Uuid $eventId,
        string $type,
        string $paymentId,
        array $payload,
    ) {
        $this->eventId = $eventId;
        $this->type = $type;
        $this->paymentId = $paymentId;
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

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
