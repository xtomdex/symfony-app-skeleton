<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Domain\DTO\ApiResponse;
use App\Domain\Enum\ResponseErrorCode;
use PHPUnit\Framework\TestCase;

final class ApiResponseEnvelopeTest extends TestCase
{
    public function test_success_to_array_has_expected_shape(): void
    {
        $dto = ApiResponse::success(['foo' => 'bar'], 200);
        $data = $dto->toArray();

        $this->assertSame(true, $data['ok']);
        $this->assertSame(['foo' => 'bar'], $data['result']);
        $this->assertNull($data['error_code']);
        $this->assertNull($data['error_message']);
        $this->assertNull($data['errors']);
        $this->assertSame(200, $data['status_code']);
    }

    public function test_failure_to_array_converts_error_code_enum_to_string(): void
    {
        $errorCode = ResponseErrorCode::Validation;

        $dto = ApiResponse::failure(
            errorCode: $errorCode,
            errorMessage: 'Validation failed',
            statusCode: 400,
            errors: ['value' => 'Value should not be blank.'],
        );

        $data = $dto->toArray();

        $this->assertSame(false, $data['ok']);
        $this->assertNull($data['result']);
        $this->assertSame('validation_failed', $data['error_code']);
        $this->assertSame('Validation failed', $data['error_message']);
        $this->assertSame(['value' => 'Value should not be blank.'], $data['errors']);
        $this->assertSame(400, $data['status_code']);
    }
}
