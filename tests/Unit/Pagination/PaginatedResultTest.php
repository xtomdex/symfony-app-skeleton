<?php

declare(strict_types=1);

namespace App\Tests\Unit\Pagination;

use App\Domain\Pagination\PaginatedResult;
use App\Domain\Pagination\PaginatedResultInterface;
use PHPUnit\Framework\TestCase;

final class PaginatedResultTest extends TestCase
{
    public function test_creation_and_getters_return_correct_values(): void
    {
        $result = new PaginatedResult(
            items: ['a', 'b', 'c'],
            currentPage: 2,
            totalPages: 5,
            pageSize: 3,
            totalItems: 15,
        );

        $this->assertSame(['a', 'b', 'c'], $result->items());
        $this->assertSame(2, $result->currentPage());
        $this->assertSame(5, $result->totalPages());
        $this->assertSame(3, $result->pageSize());
        $this->assertSame(15, $result->totalItems());
    }

    public function test_empty_items_list(): void
    {
        $result = new PaginatedResult(
            items: [],
            currentPage: 1,
            totalPages: 1,
            pageSize: 20,
            totalItems: 0,
        );

        $this->assertSame([], $result->items());
        $this->assertSame(0, $result->totalItems());
    }

    public function test_implements_paginated_result_interface(): void
    {
        $result = new PaginatedResult(
            items: [],
            currentPage: 1,
            totalPages: 1,
            pageSize: 20,
            totalItems: 0,
        );

        $this->assertInstanceOf(PaginatedResultInterface::class, $result);
    }

    public function test_map_transforms_items_and_preserves_metadata(): void
    {
        $result = new PaginatedResult(
            items: [1, 2, 3],
            currentPage: 2,
            totalPages: 5,
            pageSize: 3,
            totalItems: 15,
        );

        $mapped = $result->map(static fn(int $n): string => "item-{$n}");

        $this->assertSame(['item-1', 'item-2', 'item-3'], $mapped->items());
        $this->assertSame(2, $mapped->currentPage());
        $this->assertSame(5, $mapped->totalPages());
        $this->assertSame(3, $mapped->pageSize());
        $this->assertSame(15, $mapped->totalItems());
    }

    public function test_map_with_empty_items_returns_empty_result_with_same_metadata(): void
    {
        $result = new PaginatedResult(
            items: [],
            currentPage: 1,
            totalPages: 1,
            pageSize: 20,
            totalItems: 0,
        );

        $mapped = $result->map(static fn(mixed $item): string => 'transformed');

        $this->assertSame([], $mapped->items());
        $this->assertSame(1, $mapped->currentPage());
        $this->assertSame(1, $mapped->totalPages());
        $this->assertSame(20, $mapped->pageSize());
        $this->assertSame(0, $mapped->totalItems());
    }
}
