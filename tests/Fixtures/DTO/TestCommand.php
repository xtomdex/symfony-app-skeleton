<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\DTO;

use App\Domain\CommandBus\Contract\CommandInterface;

final class TestCommand implements CommandInterface
{
    public function __construct(
        public ?string $value = null
    ) {}
}
