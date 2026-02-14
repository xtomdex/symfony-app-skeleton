<?php

declare(strict_types=1);

namespace App\Domain\DTO;

final readonly class RedirectPayload
{
    /**
     * @param array<string, mixed> $params
     */
    public function __construct(
        public string $route,
        public array $params = [],
        public int $statusCode = 302,
    ) {}
}
