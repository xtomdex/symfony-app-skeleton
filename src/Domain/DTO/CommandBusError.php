<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use App\Domain\Enum\ResponseErrorCode;

final readonly class CommandBusError
{
    public function __construct(
        public int $statusCode,
        public string $message = 'Internal server error',
        public ResponseErrorCode $errorCode = ResponseErrorCode::Internal,
        /** @var array<string, string> */
        public array $errors = [],
        public ?RedirectPayload $redirect = null,
    ) {}
}
