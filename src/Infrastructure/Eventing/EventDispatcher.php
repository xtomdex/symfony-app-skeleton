<?php

declare(strict_types=1);

namespace App\Infrastructure\Eventing;

use App\Domain\Eventing\Contract\DomainEventInterface;
use App\Domain\Eventing\Contract\EventDispatcherInterface;

final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher
    ) {}

    public function dispatch(iterable $events): void
    {
        foreach ($events as $event) {
            if (!$event instanceof DomainEventInterface) {
                throw new \RuntimeException('Invalid event type: ' . \get_class($event));
            }
            $this->dispatcher->dispatch($event);
        }
    }
}
