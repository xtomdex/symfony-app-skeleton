<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class ValidationException extends \Exception
{
    public function __construct(
        string $message = 'Validation failed',
        private readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
