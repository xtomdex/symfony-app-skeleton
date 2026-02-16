<?php

declare(strict_types=1);

namespace App\Domain\Eventing\Contract;

interface FlusherInterface
{
    public function flush(AggregateRoot ...$roots): void;
}
