<?php

declare(strict_types=1);

namespace App\Modules\System\DTO;

use App\Modules\System\Entity\EventLog;

final readonly class EventLogListItem
{
    public function __construct(
        public string $id,
        public string $eventId,
        public string $eventName,
        public \DateTimeImmutable $recordedAt,
    ) {}

    public static function fromEntity(EventLog $eventLog): self
    {
        return new self(
            id: $eventLog->getId(),
            eventId: $eventLog->getEventId(),
            eventName: $eventLog->getEventName(),
            recordedAt: $eventLog->getRecordedAt(),
        );
    }
}
