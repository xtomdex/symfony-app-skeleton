<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Command;

use App\Domain\Contract\CommandInterface;

final class TestCommand implements CommandInterface
{
    public function __construct(
        public ?string $value = null
    ) {}
}
