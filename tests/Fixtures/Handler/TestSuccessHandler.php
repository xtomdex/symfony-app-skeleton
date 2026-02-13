<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Handler;

use App\Domain\Contract\CommandInterface;

final class TestSuccessHandler
{
    public function __invoke(CommandInterface $command): array
    {
        return ['handled' => true];
    }
}
