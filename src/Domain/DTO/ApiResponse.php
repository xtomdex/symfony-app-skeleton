<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use App\Domain\Enum\ResponseErrorCode;

final readonly class ApiResponse
{
    /**
     * @param array<string, string>|null $errors
     */
    public function __construct(
        public bool $ok,
        public int $statusCode,
        public mixed $result,
        public ?ResponseErrorCode $errorCode,
        public ?string $errorMessage,
        public ?array $errors = null,
    ) {}

    public static function success(mixed $result, int $statusCode = 200): self
    {
        return new self(
            ok: true,
            statusCode: $statusCode,
            result: $result,
            errorCode: null,
            errorMessage: null,
            errors: null,
        );
    }

    /**
     * @param array<string, string>|null $errors
     */
    public static function failure(ResponseErrorCode $errorCode, string $errorMessage, int $statusCode, ?array $errors = null, mixed $result = null): self
    {
        return new self(
            ok: false,
            statusCode: $statusCode,
            result: $result,
            errorCode: $errorCode,
            errorMessage: $errorMessage,
            errors: $errors,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'status_code' => $this->statusCode,
            'result' => $this->result,
            'error_code' => $this->errorCode?->value,
            'error_message' => $this->errorMessage,
            'errors' => $this->errors,
        ];
    }
}
