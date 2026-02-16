<?php

declare(strict_types=1);

namespace App\Domain\Eventing\Contract;

interface EventDispatcherInterface
{
    /** @param DomainEventInterface[] $events */
    public function dispatch(iterable $events): void;
}
