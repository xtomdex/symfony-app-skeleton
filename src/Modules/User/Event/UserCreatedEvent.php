<?php

declare(strict_types=1);

namespace App\Modules\User\Event;

use App\Domain\Eventing\Contract\DomainEventInterface;

final readonly class UserCreatedEvent implements DomainEventInterface
{
    public function __construct(
        public string $id,
        public string $username
    ) {}
}
