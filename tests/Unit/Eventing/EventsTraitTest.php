<?php

declare(strict_types=1);

namespace App\Tests\Unit\Eventing;

use App\Domain\Eventing\Contract\DomainEventInterface;
use App\Domain\Eventing\Trait\EventsTrait;
use PHPUnit\Framework\TestCase;

final class EventsTraitTest extends TestCase
{
    public function test_it_records_and_releases_events_and_clears_buffer(): void
    {
        $root = new FakeRoot();

        $e1 = new FakeEvent('e1');
        $e2 = new FakeEvent('e2');

        $root->recordForTest($e1);
        $root->recordForTest($e2);

        $events = $root->releaseEvents();

        self::assertCount(2, $events);
        self::assertSame($e1, $events[0]);
        self::assertSame($e2, $events[1]);

        // Buffer must be cleared after release
        self::assertSame([], $root->releaseEvents());
    }
}

final class FakeRoot
{
    use EventsTrait;

    public function recordForTest(DomainEventInterface $event): void
    {
        $this->recordEvent($event);
    }
}

final readonly class FakeEvent implements DomainEventInterface
{
    public function __construct(
        private string $id,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }
}
