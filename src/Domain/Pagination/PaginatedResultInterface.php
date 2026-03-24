<?php

declare(strict_types=1);

namespace App\Domain\Pagination;

/**
 * Contract for paginated query results.
 *
 * @template T
 */
interface PaginatedResultInterface
{
    /** @return list<T> */
    public function items(): array;

    public function currentPage(): int;

    public function totalPages(): int;

    public function pageSize(): int;

    public function totalItems(): int;
}
