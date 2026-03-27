<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\DTO;

use App\Modules\System\DTO\EventLogView;
use App\Modules\System\Entity\EventLog;
use PHPUnit\Framework\TestCase;

final class EventLogViewTest extends TestCase
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

        $view = EventLogView::fromEntity($eventLog);

        self::assertSame($eventLog->getId(), $view->id);
        self::assertSame($eventLog->getEventId(), $view->eventId);
        self::assertSame($eventLog->getEventName(), $view->eventName);
        self::assertSame($eventLog->getPayload(), $view->payload);
        self::assertSame($eventLog->getContext(), $view->context);
        self::assertSame($eventLog->getRecordedAt(), $view->recordedAt);
    }
}
