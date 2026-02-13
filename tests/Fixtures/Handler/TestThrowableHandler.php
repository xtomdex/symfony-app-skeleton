<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Handler;

use App\Domain\Contract\CommandInterface;

final class TestThrowableHandler
{
    public function __invoke(CommandInterface $command): array
    {
        throw new \RuntimeException('Error');
    }
}
