<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\Component;

use App\Modules\AdminPanel\DTO\DataColumn;
use App\Modules\AdminPanel\DTO\SortState;
use App\Modules\AdminPanel\Twig\Component\DataTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(DataTable::class)]
final class DataTableTest extends TestCase
{
    #[Test]
    public function sortUrl_with_no_current_sort_generates_asc(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $column = new DataColumn('Name', 'name', sortable: true);

        $url = $component->sortUrl($column);

        self::assertStringContainsString('sort=name', $url);
        self::assertStringContainsString('direction=asc', $url);
    }

    #[Test]
    public function sortUrl_with_same_field_sorted_asc_toggles_to_desc(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $component->sort = new SortState('name', 'asc');
        $column = new DataColumn('Name', 'name', sortable: true);

        $url = $component->sortUrl($column);

        self::assertStringContainsString('direction=desc', $url);
    }

    #[Test]
    public function sortUrl_with_same_field_sorted_desc_toggles_to_asc(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $component->sort = new SortState('name', 'desc');
        $column = new DataColumn('Name', 'name', sortable: true);

        $url = $component->sortUrl($column);

        self::assertStringContainsString('direction=asc', $url);
    }

    #[Test]
    public function sortUrl_with_different_field_resets_to_asc(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $component->sort = new SortState('email', 'desc');
        $column = new DataColumn('Name', 'name', sortable: true);

        $url = $component->sortUrl($column);

        self::assertStringContainsString('sort=name', $url);
        self::assertStringContainsString('direction=asc', $url);
    }

    #[Test]
    public function sortUrl_preserves_existing_query_parameters(): void
    {
        $component = $this->makeComponent('/admin/users', ['search' => 'john', 'status' => 'active']);
        $column = new DataColumn('Name', 'name', sortable: true);

        $url = $component->sortUrl($column);

        self::assertStringContainsString('search=john', $url);
        self::assertStringContainsString('status=active', $url);
        self::assertStringContainsString('sort=name', $url);
    }

    #[Test]
    public function sortUrl_uses_sortField_when_set(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $column = new DataColumn('Created', sortField: 'created_at', sortable: true);

        $url = $component->sortUrl($column);

        self::assertStringContainsString('sort=created_at', $url);
    }

    #[Test]
    public function sortUrl_falls_back_to_property_when_sortField_is_null(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $column = new DataColumn('Email', 'email', sortable: true);

        $url = $component->sortUrl($column);

        self::assertStringContainsString('sort=email', $url);
    }

    #[Test]
    public function sortDirection_returns_direction_when_field_matches(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $component->sort = new SortState('name', 'asc');
        $column = new DataColumn('Name', 'name');

        self::assertSame('asc', $component->sortDirection($column));
    }

    #[Test]
    public function sortDirection_returns_null_when_field_does_not_match(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $component->sort = new SortState('email', 'asc');
        $column = new DataColumn('Name', 'name');

        self::assertNull($component->sortDirection($column));
    }

    #[Test]
    public function sortDirection_returns_null_when_no_sort(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $column = new DataColumn('Name', 'name');

        self::assertNull($component->sortDirection($column));
    }

    private function makeComponent(string $path, array $queryParams): DataTable
    {
        $request = $this->createStub(Request::class);
        $request->method('getPathInfo')->willReturn($path);
        $request->query = new InputBag($queryParams);

        $requestStack = $this->createStub(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        return new DataTable($requestStack);
    }
}
