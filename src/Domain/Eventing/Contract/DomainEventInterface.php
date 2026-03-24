<?php

declare(strict_types=1);

namespace App\Domain\Eventing\Contract;

interface DomainEventInterface
{
    /** @return array<string, mixed> */
    public function getPayload(): array;
}
