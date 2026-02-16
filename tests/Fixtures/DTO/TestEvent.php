<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\DTO;

use App\Domain\Eventing\Contract\DomainEventInterface;

final readonly class TestEvent implements DomainEventInterface
{
    public function __construct(
        private string $id = 'test-event'
    ) {}

    public function getId(): string
    {
        return $this->id;
    }
}
