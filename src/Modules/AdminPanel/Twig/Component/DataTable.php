<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Component;

use App\Modules\AdminPanel\DTO\DataColumn;
use App\Modules\AdminPanel\DTO\SortState;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Pure presentation data table component.
 *
 * Receives prepared column definitions, row data, and optional sort state.
 * Renders a responsive Bootstrap table with sortable column headers.
 * Does not query databases or contain business logic.
 *
 * Usage:
 *     <twig:Admin:DataTable
 *         :columns="[new DataColumn('Name', 'name', sortable: true), ...]"
 *         :items="users"
 *         :sort="new SortState('name', 'asc')"
 *     />
 */
#[AsTwigComponent]
final class DataTable
{
    /** @var list<DataColumn> */
    public array $columns = [];

    /** @var list<array|object> */
    public array $items = [];

    /** Current sort state, null when no sorting applied */
    public ?SortState $sort = null;

    /** Message shown when items array is empty */
    public string $emptyMessage = 'No records found';

    public function __construct(private readonly RequestStack $requestStack) {}

    /**
     * Builds a URL for sorting by the given column.
     * Preserves all current query parameters, replaces sort/direction.
     * If already sorted by this field, toggles direction.
     */
    public function sortUrl(DataColumn $column): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $sortField = $column->sortField ?? $column->property;

        $direction = 'asc';
        if ($this->sort !== null && $this->sort->field === $sortField) {
            $direction = $this->sort->direction === 'asc' ? 'desc' : 'asc';
        }

        $params = $request?->query->all() ?? [];
        $params['sort'] = $sortField;
        $params['direction'] = $direction;

        $baseUrl = $request?->getPathInfo() ?? '';

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Returns the current sort direction for a given column, or null if not sorted by this column.
     */
    public function sortDirection(DataColumn $column): ?string
    {
        $sortField = $column->sortField ?? $column->property;

        if ($this->sort !== null && $this->sort->field === $sortField) {
            return $this->sort->direction;
        }

        return null;
    }
}
