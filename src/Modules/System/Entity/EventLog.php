<?php

declare(strict_types=1);

namespace App\Modules\System\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'system_event_log')]
#[ORM\Index(name: 'idx_system_event_log_event_name', columns: ['event_name'])]
#[ORM\Index(name: 'idx_system_event_log_recorded_at', columns: ['recorded_at'])]
final class EventLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 64)]
    private string $eventId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $eventName;

    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $context;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $recordedAt;

    private function __construct(
        string $id,
        string $eventId,
        string $eventName,
        array $payload,
        ?array $context,
        \DateTimeImmutable $recordedAt,
    ) {
        $this->id = $id;
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->context = $context;
        $this->recordedAt = $recordedAt;
    }

    public static function create(
        string $id,
        string $eventId,
        string $eventName,
        array $payload = [],
        ?array $context = null,
        ?\DateTimeImmutable $recordedAt = null,
    ): self {
        return new self(
            id: $id,
            eventId: $eventId,
            eventName: $eventName,
            payload: $payload,
            context: $context,
            recordedAt: $recordedAt ?? new \DateTimeImmutable(),
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function getRecordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }
}
