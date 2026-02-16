<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Service\CommandBus\Handler;

use App\Domain\CommandBus\Contract\CommandInterface;

final class TestThrowableHandler
{
    public function __invoke(CommandInterface $command): array
    {
        throw new \RuntimeException('Error');
    }
}
