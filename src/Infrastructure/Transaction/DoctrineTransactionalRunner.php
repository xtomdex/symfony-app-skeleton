<?php

declare(strict_types=1);

namespace App\Infrastructure\Transaction;

use App\Domain\Eventing\Contract\DomainEventInterface;
use App\Domain\Eventing\Contract\EventDispatcherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTransactionalRunner implements TransactionalRunnerInterface
{
    private bool $active = false;

    /** @var array<int, DomainEventInterface> */
    private array $queuedEvents = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function isActive(): bool
    {
        return $this->active;
    }

    public function enqueueEvents(array $events): void
    {
        foreach ($events as $event) {
            if (!$event instanceof DomainEventInterface) {
                throw new \InvalidArgumentException('Invalid event: ' . get_debug_type($event));
            }
            $this->queuedEvents[] = $event;
        }
    }

    public function run(callable $callback): mixed
    {
        // v1 rule: no nested runner scopes
        if ($this->active) {
            throw new \LogicException('TransactionalRunner is already active. Nested run() is not allowed in v1.');
        }

        $conn = $this->em->getConnection();
        $this->active = true;
        $this->queuedEvents = [];

        $conn->beginTransaction();

        try {
            $result = $callback();

            // Important: make sure pending changes are persisted
            $this->em->flush();

            $conn->commit();

            // Dispatch events ONLY after successful commit
            foreach ($this->queuedEvents as $event) {
                $this->dispatcher->dispatch($event);
            }

            return $result;
        } catch (\Throwable $e) {
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            throw $e;
        } finally {
            $this->active = false;
            $this->queuedEvents = [];
        }
    }
}
