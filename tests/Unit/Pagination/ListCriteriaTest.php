<?php

declare(strict_types=1);

namespace App\Tests\Unit\Pagination;

use App\Domain\Pagination\ListCriteria;
use PHPUnit\Framework\TestCase;

final class ListCriteriaTest extends TestCase
{
    public function test_creation_with_defaults(): void
    {
        $criteria = new ListCriteria();

        $this->assertSame(1, $criteria->page);
        $this->assertSame(20, $criteria->pageSize);
        $this->assertNull($criteria->sortField);
        $this->assertSame('asc', $criteria->sortDirection);
    }

    public function test_creation_with_all_params(): void
    {
        $criteria = new ListCriteria(
            page: 3,
            pageSize: 50,
            sortField: 'name',
            sortDirection: 'desc',
        );

        $this->assertSame(3, $criteria->page);
        $this->assertSame(50, $criteria->pageSize);
        $this->assertSame('name', $criteria->sortField);
        $this->assertSame('desc', $criteria->sortDirection);
    }

    public function test_creation_with_custom_values(): void
    {
        $criteria = new ListCriteria(page: 5, pageSize: 10);

        $this->assertSame(5, $criteria->page);
        $this->assertSame(10, $criteria->pageSize);
        $this->assertNull($criteria->sortField);
        $this->assertSame('asc', $criteria->sortDirection);
    }
}
