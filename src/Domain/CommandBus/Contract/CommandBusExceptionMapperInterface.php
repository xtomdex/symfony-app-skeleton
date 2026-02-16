<?php

declare(strict_types=1);

namespace App\Domain\CommandBus\Contract;

use App\Domain\CommandBus\DTO\CommandBusError;

interface CommandBusExceptionMapperInterface
{
    public function map(\Throwable $e): CommandBusError;
}
