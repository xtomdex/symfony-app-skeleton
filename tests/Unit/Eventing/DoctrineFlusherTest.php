<?php

declare(strict_types=1);

namespace App\Tests\Unit\Eventing;

use App\Domain\Eventing\Contract\AggregateRoot;
use App\Domain\Eventing\Contract\DomainEventInterface;
use App\Domain\Eventing\Contract\EventDispatcherInterface;
use App\Infrastructure\Eventing\DoctrineFlusher;
use App\Infrastructure\Transaction\TransactionalRunnerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class DoctrineFlusherTest extends TestCase
{
    public function test_flush_calls_em_flush_then_dispatches_collected_events(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $runner = $this->createStub(TransactionalRunnerInterface::class);

        $em->expects(self::once())->method('flush');

        $event1 = new FakeEventForFlusher('e1');
        $event2 = new FakeEventForFlusher('e2');
        $root = new FakeRootForFlusher([$event1, $event2]);

        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (iterable $events) use ($event1, $event2): bool {
                $events = is_array($events) ? $events : iterator_to_array($events);
                return $events === [$event1, $event2];
            }));

        $flusher = new DoctrineFlusher($em, $dispatcher, $runner);
        $flusher->flush($root);
    }

    public function test_flush_deduplicates_roots_by_object_identity(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $runner = $this->createStub(TransactionalRunnerInterface::class);

        $em->expects(self::once())->method('flush');

        $event = new FakeEventForFlusher('e1');
        $root = new FakeRootForFlusher([$event]);

        // Same object passed twice => must dispatch only once
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (iterable $events) use ($event): bool {
                $events = is_array($events) ? $events : iterator_to_array($events);
                return $events === [$event];
            }));

        $flusher = new DoctrineFlusher($em, $dispatcher, $runner);
        $flusher->flush($root, $root);
    }

    public function test_flush_throws_when_root_releases_non_domain_event(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $dispatcher = $this->createStub(EventDispatcherInterface::class);
        $runner = $this->createStub(TransactionalRunnerInterface::class);

        $root = new FakeRootForFlusher([new \stdClass()]); // invalid

        $flusher = new DoctrineFlusher($em, $dispatcher, $runner);

        $this->expectException(\InvalidArgumentException::class);
        $flusher->flush($root);
    }

    public function test_flush_when_runner_active_flushes_and_enqueues_events_without_db_transaction_and_without_dispatch(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $conn = $this->createMock(Connection::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $runner = $this->createMock(TransactionalRunnerInterface::class);

        $runner->expects(self::once())
            ->method('isActive')
            ->willReturn(true);

        // When runner is active: no explicit DB transaction management here.
        $em->expects(self::once())->method('flush');
        $em->expects(self::never())->method('getConnection'); // optional: ensures we don't touch DBAL tx

        $runner->expects(self::once())
            ->method('enqueueEvents')
            ->with(self::callback(function (iterable $events): bool {
                $events = is_array($events) ? $events : iterator_to_array($events);
                return count($events) === 2
                    && $events[0] instanceof FakeEventForFlusher
                    && $events[1] instanceof FakeEventForFlusher
                    && $events[0]->getId() === 'e1'
                    && $events[1]->getId() === 'e2';
            }));

        $dispatcher->expects(self::never())->method('dispatch');

        $flusher = new DoctrineFlusher($em, $dispatcher, $runner);

        $root = new FakeRootForFlusher([new FakeEventForFlusher('e1'), new FakeEventForFlusher('e2')]);

        $flusher->flush($root);
    }

    public function test_flush_when_runner_not_active_begins_commits_and_dispatches_after_commit(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $conn = $this->createMock(Connection::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $runner = $this->createMock(TransactionalRunnerInterface::class);

        $runner->expects(self::once())
            ->method('isActive')
            ->willReturn(false);

        $em->expects(self::once())
            ->method('getConnection')
            ->willReturn($conn);

        $conn->expects(self::once())->method('beginTransaction');
        $em->expects(self::once())->method('flush');
        $conn->expects(self::once())->method('commit');

        $dispatcher->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (iterable $events): bool {
                $events = is_array($events) ? $events : iterator_to_array($events);
                return count($events) === 1
                    && $events[0] instanceof FakeEventForFlusher
                    && $events[0]->getId() === 'e1';
            }));

        $runner->expects(self::never())->method('enqueueEvents');

        $flusher = new DoctrineFlusher($em, $dispatcher, $runner);

        $root = new FakeRootForFlusher([new FakeEventForFlusher('e1')]);

        $flusher->flush($root);
    }

    public function test_flush_when_runner_not_active_rolls_back_and_does_not_dispatch_on_flush_exception(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $conn = $this->createMock(Connection::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $runner = $this->createMock(TransactionalRunnerInterface::class);

        $runner->expects(self::once())
            ->method('isActive')
            ->willReturn(false);

        $em->expects(self::once())
            ->method('getConnection')
            ->willReturn($conn);

        $conn->expects(self::once())->method('beginTransaction');

        $em->expects(self::once())
            ->method('flush')
            ->willThrowException(new \RuntimeException('DB failed'));

        // Commit must not happen
        $conn->expects(self::never())->method('commit');

        // Rollback must happen
        $conn->expects(self::once())->method('isTransactionActive')->willReturn(true);
        $conn->expects(self::once())->method('rollBack');

        // Must not dispatch events if flush failed
        $dispatcher->expects(self::never())->method('dispatch');

        $runner->expects(self::never())->method('enqueueEvents');

        $flusher = new DoctrineFlusher($em, $dispatcher, $runner);

        $this->expectException(\RuntimeException::class);

        $root = new FakeRootForFlusher([new FakeEventForFlusher('e1')]);
        $flusher->flush($root);
    }
}

final readonly class FakeEventForFlusher implements DomainEventInterface
{
    public function __construct(private string $id) {}

    public function getId(): string
    {
        return $this->id;
    }
}

final class FakeRootForFlusher implements AggregateRoot
{
    /** @var array<int, mixed> */
    private array $events;

    /** @param array<int, mixed> $events */
    public function __construct(array $events)
    {
        $this->events = $events;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
