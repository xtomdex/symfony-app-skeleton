<?php

declare(strict_types=1);

namespace App\Tests\Unit\Eventing;

use App\Domain\Eventing\Contract\AggregateRoot;
use App\Domain\Eventing\Contract\DomainEventInterface;
use App\Domain\Eventing\Contract\EventDispatcherInterface;
use App\Domain\Eventing\Trait\EventsTrait;
use App\Infrastructure\Eventing\Flusher;
use PHPUnit\Framework\TestCase;

final class FlusherTest extends TestCase
{
    public function test_it_deduplicates_roots_by_object_identity_not_by_class(): void
    {
        $dispatcher = new InMemoryDispatcherForFlusher();
        $flusher = new Flusher($dispatcher);

        $root = new FakeAggregateRootForFlusher();
        $root->recordForTest(new FakeEventForFlusher('e1'));

        // Passing the same object twice must still dispatch only once
        $flusher->flush($root, $root);

        self::assertCount(1, $dispatcher->events);
        self::assertSame('e1', $dispatcher->events[0]->getId());
    }

    public function test_it_dispatches_events_from_multiple_roots_in_one_flush_in_order(): void
    {
        $dispatcher = new InMemoryDispatcherForFlusher();
        $flusher = new Flusher($dispatcher);

        $a = new FakeAggregateRootForFlusher();
        $b = new FakeAggregateRootForFlusher();

        $a->recordForTest(new FakeEventForFlusher('a1'));
        $a->recordForTest(new FakeEventForFlusher('a2'));
        $b->recordForTest(new FakeEventForFlusher('b1'));

        $flusher->flush($a, $b);

        self::assertSame(['a1', 'a2', 'b1'], array_map(
            static fn (DomainEventInterface $e) => $e->getId(),
            $dispatcher->events
        ));

        // Roots must be cleared after flush
        self::assertSame([], $a->releaseEvents());
        self::assertSame([], $b->releaseEvents());
    }
}

final class InMemoryDispatcherForFlusher implements EventDispatcherInterface
{
    /** @var list<DomainEventInterface> */
    public array $events = [];

    public function dispatch(iterable $events): void
    {
        foreach ($events as $event) {
            $this->events[] = $event;
        }
    }
}

final class FakeAggregateRootForFlusher implements AggregateRoot
{
    use EventsTrait;

    public function recordForTest(DomainEventInterface $event): void
    {
        $this->recordEvent($event);
    }
}

final readonly class FakeEventForFlusher implements DomainEventInterface
{
    public function __construct(
        private string $id,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }
}
