<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class RedirectException extends \RuntimeException
{
    /**
     * @param array<string, mixed> $params
     */
    public function __construct(
        public readonly string $route,
        public readonly array $params = [],
        public readonly int $statusCode = 302,
        string $message = '',
    ) {
        parent::__construct($message, $statusCode);
    }
}
