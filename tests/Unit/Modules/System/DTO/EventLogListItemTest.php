<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\DTO;

use App\Modules\System\DTO\EventLogListItem;
use App\Modules\System\Entity\EventLog;
use PHPUnit\Framework\TestCase;

final class EventLogListItemTest extends TestCase
{
    public function test_from_entity(): void
    {
        $recordedAt = new \DateTimeImmutable('2026-01-15 10:00:00');

        $eventLog = EventLog::create(
            id: 'log-id-1',
            eventId: 'event-id-1',
            eventName: 'user.created',
            payload: ['userId' => 'abc'],
            context: ['ip' => '127.0.0.1'],
            recordedAt: $recordedAt,
        );

        $item = EventLogListItem::fromEntity($eventLog);

        self::assertSame($eventLog->getId(), $item->id);
        self::assertSame($eventLog->getEventId(), $item->eventId);
        self::assertSame($eventLog->getEventName(), $item->eventName);
        self::assertSame($eventLog->getRecordedAt(), $item->recordedAt);
        self::assertFalse(property_exists($item, 'payload'));
        self::assertFalse(property_exists($item, 'context'));
    }
}
