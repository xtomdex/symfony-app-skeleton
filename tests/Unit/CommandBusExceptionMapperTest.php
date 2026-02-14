<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\DTO\CommandBusError;
use App\Domain\Enum\ResponseErrorCode;
use App\Domain\Exception\ValidationException;
use App\Infrastructure\CommandBus\CommandBusExceptionMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class CommandBusExceptionMapperTest extends TestCase
{
    public function test_it_maps_validation_exception_to_validation_failed(): void
    {
        $mapper = new CommandBusExceptionMapper();
        $message = 'Validation failed';
        $errors = ['value' => 'Value should not be blank.'];
        $e = new ValidationException($message, $errors);

        $error = $mapper->map($e);

        $this->assertInstanceOf(CommandBusError::class, $error);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $error->statusCode);
        $this->assertSame(ResponseErrorCode::Validation, $error->errorCode);
        $this->assertSame($errors, $error->errors);
        $this->assertSame($message, $error->message);
    }
}
