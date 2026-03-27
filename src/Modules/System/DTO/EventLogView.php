<?php

declare(strict_types=1);

namespace App\Modules\System\DTO;

use App\Modules\System\Entity\EventLog;

final readonly class EventLogView
{
    public function __construct(
        public string $id,
        public string $eventId,
        public string $eventName,
        public array $payload,
        public ?array $context,
        public \DateTimeImmutable $recordedAt,
    ) {}

    public static function fromEntity(EventLog $eventLog): self
    {
        return new self(
            id: $eventLog->getId(),
            eventId: $eventLog->getEventId(),
            eventName: $eventLog->getEventName(),
            payload: $eventLog->getPayload(),
            context: $eventLog->getContext(),
            recordedAt: $eventLog->getRecordedAt(),
        );
    }
}
