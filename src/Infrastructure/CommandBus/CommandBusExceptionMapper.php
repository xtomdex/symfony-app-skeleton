<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus;

use App\Domain\Contract\CommandBusExceptionMapperInterface;
use App\Domain\DTO\CommandBusError;
use App\Domain\DTO\RedirectPayload;
use App\Domain\Exception\ForbiddenException;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\RedirectException;
use App\Domain\Exception\ValidationException;

final class CommandBusExceptionMapper implements CommandBusExceptionMapperInterface
{
    public function map(\Throwable $e): CommandBusError
    {
        return match (true) {
            $e instanceof ValidationException => new CommandBusError(
                statusCode: 400,
                message: $e->getMessage() ?: 'Validation failed',
                errors: $e->getErrors(),
            ),
            $e instanceof NotFoundException => new CommandBusError(404, $e->getMessage() ?: 'Not found'),
            $e instanceof ForbiddenException => new CommandBusError(403, $e->getMessage() ?: 'Forbidden'),
            //$e instanceof AuthenticationException => new CommandBusError(401, $e->getMessage() ?: 'Unauthorized'),
            $e instanceof RedirectException => new CommandBusError(
                statusCode: $e->statusCode,
                message: $e->getMessage(),
                redirect: new RedirectPayload($e->route, $e->params, $e->statusCode),
            ),
            default => new CommandBusError(500, 'Internal server error'),
        };
    }
}
