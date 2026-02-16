<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Service\CommandBus\Handler;

use App\Domain\CommandBus\Contract\CommandInterface;

final class TestFailingHandler
{
    public function __invoke(CommandInterface $command): mixed
    {
        throw new \RuntimeException('Handler should not be called');
    }
}
