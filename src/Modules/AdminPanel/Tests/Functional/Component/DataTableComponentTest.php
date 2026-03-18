<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Component;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class DataTableComponentTest extends KernelTestCase
{
    #[Test]
    public function renders_column_headers(): void
    {
        $html = $this->renderTable();

        self::assertStringContainsString('Name', $html);
        self::assertStringContainsString('Email', $html);
        self::assertStringContainsString('Status', $html);
    }

    #[Test]
    public function renders_row_data(): void
    {
        $html = $this->renderTable();

        self::assertStringContainsString('John Doe', $html);
        self::assertStringContainsString('john@example.com', $html);
        self::assertStringContainsString('Active', $html);
        self::assertStringContainsString('Jane Smith', $html);
    }

    #[Test]
    public function renders_sort_indicator_for_active_sort(): void
    {
        $html = $this->renderTable();

        // 'name' column is sorted asc — should show chevron-up
        self::assertStringContainsString('tabler-chevron-up', $html);
    }

    #[Test]
    public function renders_sort_link_for_sortable_columns(): void
    {
        $html = $this->renderTable();

        self::assertStringContainsString('?sort=name', $html);
    }

    #[Test]
    public function renders_pagination(): void
    {
        $html = $this->renderTable();

        self::assertStringContainsString('page-item', $html);
        self::assertStringContainsString('?page=2', $html);
    }

    #[Test]
    public function renders_empty_state_when_no_items(): void
    {
        $html = $this->renderTableEmpty();

        self::assertStringContainsString('admin.table.empty', $html);
    }

    #[Test]
    public function does_not_render_pagination_for_single_page(): void
    {
        $html = $this->renderTableSinglePage();

        self::assertStringNotContainsString('page-item', $html);
    }

    private function renderTable(): string
    {
        return $this->render([
            'columns' => [
                ['key' => 'name', 'label' => 'Name', 'sortable' => true],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'items' => [
                ['name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'Active'],
                ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'Inactive'],
            ],
            'sort' => ['key' => 'name', 'direction' => 'asc'],
            'pagination' => ['page' => 1, 'pages' => 3, 'total' => 25],
        ]);
    }

    private function renderTableEmpty(): string
    {
        return $this->render([
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
            ],
            'items' => [],
            'sort' => null,
            'pagination' => null,
        ]);
    }

    private function renderTableSinglePage(): string
    {
        return $this->render([
            'columns' => [
                ['key' => 'name', 'label' => 'Name'],
            ],
            'items' => [
                ['name' => 'Only One'],
            ],
            'sort' => null,
            'pagination' => ['page' => 1, 'pages' => 1, 'total' => 1],
        ]);
    }

    private function render(array $context): string
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel_test/datatable_test.html.twig', $context);
    }
}
