<?php

declare(strict_types=1);

namespace App\Domain\Pagination;

/**
 * Universal input DTO for paginated list queries.
 *
 * Controllers build this from request query parameters.
 * Use case commands include it alongside domain-specific filters.
 */
final readonly class ListCriteria
{
    public function __construct(
        public int $page = 1,
        public int $pageSize = 20,
        public ?string $sortField = null,
        public string $sortDirection = 'asc',
    ) {}
}
