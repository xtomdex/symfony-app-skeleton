<?php

declare(strict_types=1);

namespace App\Infrastructure\Eventing;

use App\Domain\Eventing\Contract\AggregateRoot;
use App\Domain\Eventing\Contract\DomainEventInterface;
use App\Domain\Eventing\Contract\EventDispatcherInterface;
use App\Domain\Eventing\Contract\FlusherInterface;
use App\Infrastructure\Transaction\TransactionalRunnerInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineFlusher implements FlusherInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher,
        private TransactionalRunnerInterface $runner,
    ) {}

    public function flush(AggregateRoot ...$roots): void
    {
        $events = $this->collectEventsFromRoots($roots);

        // If runner owns the transaction, we only enqueue events.
        if ($this->runner->isActive()) {
            $this->runner->enqueueEvents($events);
            return;
        }

        // Otherwise, default behavior: transaction is owned here.
        $conn = $this->em->getConnection();
        $conn->beginTransaction();

        try {
            $this->em->flush();
            $conn->commit();

            $this->dispatcher->dispatch($events);
        } catch (\Throwable $e) {
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @param AggregateRoot[] $roots
     * @return DomainEventInterface[]
     */
    private function collectEventsFromRoots(array $roots): array
    {
        $uniqueRoots = $this->deduplicateRoots($roots);

        $events = [];
        foreach ($uniqueRoots as $root) {
            foreach ($root->releaseEvents() as $event) {
                if (!$event instanceof DomainEventInterface) {
                    throw new \InvalidArgumentException('Invalid event provided: ' . get_class($event));
                }

                $events[] = $event;
            }
        }

        return $events;
    }

    /**
     * Deduplicate by object identity.
     *
     * @param AggregateRoot[] $roots
     * @return AggregateRoot[]
     */
    private function deduplicateRoots(array $roots): array
    {
        $seen = [];
        $unique = [];

        foreach ($roots as $root) {
            $id = spl_object_id($root); // int identity for this object instance

            if (isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $unique[] = $root;
        }

        return $unique;
    }
}
