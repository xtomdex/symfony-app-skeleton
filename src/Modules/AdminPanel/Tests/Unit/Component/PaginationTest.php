<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\Component;

use App\Modules\AdminPanel\Twig\Component\Pagination;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(Pagination::class)]
final class PaginationTest extends TestCase
{
    #[Test]
    public function pageUrl_builds_correct_url_with_page_param(): void
    {
        $component = $this->makeComponent('/admin/users', []);

        $url = $component->pageUrl(3);

        self::assertStringContainsString('/admin/users', $url);
        self::assertStringContainsString('page=3', $url);
    }

    #[Test]
    public function pageUrl_preserves_existing_query_params(): void
    {
        $component = $this->makeComponent('/admin/users', ['search' => 'jane', 'sort' => 'name']);

        $url = $component->pageUrl(2);

        self::assertStringContainsString('search=jane', $url);
        self::assertStringContainsString('sort=name', $url);
        self::assertStringContainsString('page=2', $url);
    }

    #[Test]
    public function pageSizeUrl_resets_page_to_1(): void
    {
        $component = $this->makeComponent('/admin/users', ['page' => '5']);

        $url = $component->pageSizeUrl(50);

        self::assertStringContainsString('page=1', $url);
        self::assertStringContainsString('pageSize=50', $url);
    }

    #[Test]
    public function getPageRange_centers_around_current_page(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $component->currentPage = 5;
        $component->totalPages = 10;

        $range = $component->getPageRange();

        self::assertSame([3, 4, 5, 6, 7], $range);
    }

    #[Test]
    public function getPageRange_clamps_to_1_when_near_start(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $component->currentPage = 1;
        $component->totalPages = 10;

        $range = $component->getPageRange();

        self::assertSame([1, 2, 3, 4, 5], $range);
    }

    #[Test]
    public function getPageRange_clamps_to_totalPages_when_near_end(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $component->currentPage = 10;
        $component->totalPages = 10;

        $range = $component->getPageRange();

        self::assertSame([6, 7, 8, 9, 10], $range);
    }

    #[Test]
    public function getPageRange_with_few_pages_returns_exact_range(): void
    {
        $component = $this->makeComponent('/admin/users', []);
        $component->currentPage = 2;
        $component->totalPages = 3;

        $range = $component->getPageRange();

        self::assertSame([1, 2, 3], $range);
    }

    private function makeComponent(string $path, array $queryParams): Pagination
    {
        $request = $this->createStub(Request::class);
        $request->method('getPathInfo')->willReturn($path);
        $request->query = new InputBag($queryParams);

        $requestStack = $this->createStub(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        return new Pagination($requestStack);
    }
}
