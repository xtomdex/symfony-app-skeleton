<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Component;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Standalone pagination component.
 *
 * Renders page navigation and a page-size selector.
 * Builds URLs from the current request, preserving existing query parameters.
 * Can be used alongside DataTable or independently.
 *
 * Usage:
 *     <twig:Admin:Pagination
 *         :currentPage="page"
 *         :totalPages="totalPages"
 *         :pageSize="pageSize"
 *     />
 */
#[AsTwigComponent]
final class Pagination
{
    public int $currentPage = 1;
    public int $totalPages = 1;
    public int $pageSize = 20;

    /** @var list<int> */
    public array $pageSizeOptions = [10, 20, 50, 100];

    public function __construct(private readonly RequestStack $requestStack) {}

    /**
     * Builds URL for a specific page. Preserves all current query parameters.
     */
    public function pageUrl(int $page): string
    {
        return $this->buildUrl(['page' => $page]);
    }

    /**
     * Builds URL for changing page size. Resets to page 1.
     */
    public function pageSizeUrl(int $size): string
    {
        return $this->buildUrl(['page' => 1, 'pageSize' => $size]);
    }

    /**
     * Returns the range of page numbers to display.
     * Shows up to 5 pages centered around current page.
     */
    public function getPageRange(): array
    {
        $start = max(1, $this->currentPage - 2);
        $end = min($this->totalPages, $start + 4);
        $start = max(1, $end - 4);

        return range($start, $end);
    }

    private function buildUrl(array $overrides): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $params = $request?->query->all() ?? [];
        $params = array_merge($params, $overrides);
        $baseUrl = $request?->getPathInfo() ?? '';

        return $baseUrl . '?' . http_build_query($params);
    }
}
