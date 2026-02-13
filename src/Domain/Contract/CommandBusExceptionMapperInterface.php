<?php

declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\DTO\CommandBusError;

interface CommandBusExceptionMapperInterface
{
    public function map(\Throwable $e): CommandBusError;
}
