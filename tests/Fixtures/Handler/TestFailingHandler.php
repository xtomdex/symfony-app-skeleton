<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Handler;

use App\Domain\Contract\CommandInterface;

final class TestFailingHandler
{
    public function __invoke(CommandInterface $command): mixed
    {
        throw new \RuntimeException('Handler should not be called');
    }
}
