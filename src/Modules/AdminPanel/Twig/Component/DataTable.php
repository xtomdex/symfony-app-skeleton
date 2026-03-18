<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Pure presentation data table component.
 *
 * Receives prepared data and renders a table with optional sorting indicators
 * and pagination. Does not query databases or contain business logic.
 *
 * Usage:
 *     <twig:Admin:DataTable
 *         :columns="[
 *             {key: 'name', label: 'Name', sortable: true},
 *             {key: 'email', label: 'Email'},
 *             {key: 'status', label: 'Status'},
 *         ]"
 *         :items="users"
 *         :sort="{key: 'name', direction: 'asc'}"
 *         :pagination="{page: 1, pages: 10, total: 95}"
 *     />
 */
#[AsTwigComponent]
final class DataTable
{
    /**
     * Column definitions.
     * Each column: ['key' => string, 'label' => string, 'sortable' => bool].
     *
     * @var list<array{key: string, label: string, sortable?: bool}>
     */
    public array $columns = [];

    /**
     * Row data. Each item is an associative array keyed by column keys.
     *
     * @var list<array<string, mixed>>
     */
    public array $items = [];

    /**
     * Current sort state: ['key' => string, 'direction' => 'asc'|'desc'] or null.
     *
     * @var array{key: string, direction: string}|null
     */
    public ?array $sort = null;

    /**
     * Pagination state: ['page' => int, 'pages' => int, 'total' => int] or null.
     *
     * @var array{page: int, pages: int, total: int}|null
     */
    public ?array $pagination = null;
}
