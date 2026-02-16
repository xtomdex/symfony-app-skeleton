<?php

declare(strict_types=1);

namespace App\Infrastructure\Eventing;

use App\Domain\Eventing\Contract\AggregateRoot;
use App\Domain\Eventing\Contract\DomainEventInterface;
use App\Domain\Eventing\Contract\EventDispatcherInterface;
use App\Domain\Eventing\Contract\FlusherInterface;

final readonly class Flusher implements FlusherInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher
    ) {}

    public function flush(AggregateRoot ...$roots): void
    {
        // 1) TODO: persist/flush transaction here (Doctrine or any other persistence)
        // - begin transaction
        // - flush changes
        // - commit
        //
        // Important rule for the future: dispatch events ONLY after successful commit.

        // 2) Collect events from unique roots
        $events = $this->collectEventsFromRoots($roots);

        // 3) Dispatch
        $this->dispatcher->dispatch($events);
    }

    /**
     * @param AggregateRoot $roots
     * @return AggregateRoot
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
     * @param AggregateRoot $roots
     * @return AggregateRoot
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
