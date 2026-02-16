<?php

declare(strict_types=1);

namespace App\Domain\Eventing\Contract;

interface AggregateRoot
{
    /** @return DomainEventInterface[] */
    public function releaseEvents(): array;
}
