<?php

declare(strict_types=1);

namespace App\Infrastructure\Transaction;

use App\Domain\Eventing\Contract\DomainEventInterface;

interface TransactionalRunnerInterface
{
    public function isActive(): bool;

    /**
     * @param callable(): mixed $callback
     */
    public function run(callable $callback): mixed;

    /**
     * @param array<int, DomainEventInterface> $events
     */
    public function enqueueEvents(array $events): void;
}
