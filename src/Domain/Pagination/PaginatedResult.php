<?php

declare(strict_types=1);

namespace App\Domain\Pagination;

/**
 * Default implementation of PaginatedResultInterface.
 *
 * @template T
 * @implements PaginatedResultInterface<T>
 */
final readonly class PaginatedResult implements PaginatedResultInterface
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        private array $items,
        private int $currentPage,
        private int $totalPages,
        private int $pageSize,
        private int $totalItems,
    ) {}

    /** @return list<T> */
    public function items(): array
    {
        return $this->items;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function totalPages(): int
    {
        return $this->totalPages;
    }

    public function pageSize(): int
    {
        return $this->pageSize;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * Transform items while preserving pagination metadata.
     *
     * Usage in handler:
     *     return $this->repository->findPaginated($criteria)
     *         ->map(UserListItem::fromEntity(...));
     *
     * @template U
     * @param callable(T): U $mapper
     * @return self<U>
     */
    public function map(callable $mapper): self
    {
        return new self(
            items: array_map($mapper, $this->items),
            currentPage: $this->currentPage,
            totalPages: $this->totalPages,
            pageSize: $this->pageSize,
            totalItems: $this->totalItems,
        );
    }
}
