<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus;

use App\Domain\CommandBus\Contract\CommandBusExceptionMapperInterface;
use App\Domain\CommandBus\DTO\CommandBusError;
use App\Domain\CommandBus\DTO\RedirectPayload;
use App\Domain\CommandBus\Enum\ResponseErrorCode;
use App\Domain\CommandBus\Exception\ForbiddenException;
use App\Domain\CommandBus\Exception\NotFoundException;
use App\Domain\CommandBus\Exception\RedirectException;
use App\Domain\CommandBus\Exception\ValidationException;

final class CommandBusExceptionMapper implements CommandBusExceptionMapperInterface
{
    public function map(\Throwable $e): CommandBusError
    {
        return match (true) {
            $e instanceof ValidationException => new CommandBusError(
                statusCode: 400,
                message: $e->getMessage() ?: 'Validation failed',
                errorCode: ResponseErrorCode::Validation,
                errors: $e->getErrors(),
            ),
            $e instanceof NotFoundException => new CommandBusError(
                statusCode: 404,
                message: $e->getMessage() ?: 'Not found',
                errorCode: ResponseErrorCode::NotFound
            ),
            $e instanceof ForbiddenException => new CommandBusError(
                statusCode: 403,
                message: $e->getMessage() ?: 'Forbidden',
                errorCode: ResponseErrorCode::Forbidden
            ),
            //$e instanceof AuthenticationException => new CommandBusError(401, $e->getMessage() ?: 'Unauthorized'),
            $e instanceof RedirectException => new CommandBusError(
                statusCode: $e->statusCode,
                message: $e->getMessage(),
                redirect: new RedirectPayload($e->route, $e->params, $e->statusCode),
            ),
            default => new CommandBusError(500),
        };
    }
}
