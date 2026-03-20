<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Component;

use App\Modules\AdminPanel\DTO\DataColumn;
use App\Modules\AdminPanel\DTO\SortState;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class DataTableComponentTest extends KernelTestCase
{
    #[Test]
    public function renders_column_headers(): void
    {
        $html = $this->render(
            columns: [
                new DataColumn('Name', 'name'),
                new DataColumn('Email', 'email'),
                new DataColumn('Status', 'status'),
            ],
            items: [['name' => 'John', 'email' => 'john@example.com', 'status' => 'Active']],
        );

        self::assertStringContainsString('Name', $html);
        self::assertStringContainsString('Email', $html);
        self::assertStringContainsString('Status', $html);
    }

    #[Test]
    public function renders_row_data_via_property_access(): void
    {
        $html = $this->render(
            columns: [new DataColumn('Name', 'name')],
            items: [['name' => 'Jane Doe']],
        );

        self::assertStringContainsString('Jane Doe', $html);
    }

    #[Test]
    public function renders_row_data_via_template(): void
    {
        $html = $this->render(
            columns: [
                new DataColumn(
                    label: 'Name',
                    template: '@admin_panel_test/datatable_test_cell.html.twig',
                ),
            ],
            items: [['name' => 'john']],
        );

        self::assertStringContainsString('test-cell', $html);
        self::assertStringContainsString('JOHN', $html);
    }

    #[Test]
    public function template_column_takes_priority_over_property(): void
    {
        $html = $this->render(
            columns: [
                new DataColumn(
                    label: 'Name',
                    property: 'name',
                    template: '@admin_panel_test/datatable_test_cell.html.twig',
                ),
            ],
            items: [['name' => 'alice']],
        );

        // Template output is uppercased via |upper filter; direct property would be lowercase
        self::assertStringContainsString('ALICE', $html);
        self::assertStringContainsString('test-cell', $html);
    }

    #[Test]
    public function sortable_column_renders_as_link(): void
    {
        $html = $this->render(
            columns: [new DataColumn('Name', 'name', sortable: true)],
            items: [['name' => 'John']],
        );

        self::assertStringContainsString('<a ', $html);
        self::assertStringContainsString('sort=name', $html);
    }

    #[Test]
    public function non_sortable_column_renders_as_plain_text(): void
    {
        $html = $this->render(
            columns: [new DataColumn('Email', 'email')],
            items: [['email' => 'test@example.com']],
        );

        self::assertStringNotContainsString('sort=email', $html);
    }

    #[Test]
    public function active_sort_asc_shows_chevron_up_icon(): void
    {
        $html = $this->render(
            columns: [new DataColumn('Name', 'name', sortable: true)],
            items: [['name' => 'John']],
            sort: new SortState('name', 'asc'),
        );

        self::assertStringContainsString('tabler-chevron-up', $html);
    }

    #[Test]
    public function active_sort_desc_shows_chevron_down_icon(): void
    {
        $html = $this->render(
            columns: [new DataColumn('Name', 'name', sortable: true)],
            items: [['name' => 'John']],
            sort: new SortState('name', 'desc'),
        );

        self::assertStringContainsString('tabler-chevron-down', $html);
    }

    #[Test]
    public function empty_items_renders_empty_message_with_colspan(): void
    {
        $html = $this->render(
            columns: [new DataColumn('Name', 'name'), new DataColumn('Email', 'email')],
            items: [],
        );

        self::assertStringContainsString('No records found', $html);
        self::assertStringContainsString('colspan="2"', $html);
    }

    #[Test]
    public function custom_empty_message_is_displayed(): void
    {
        $html = $this->render(
            columns: [new DataColumn('Name', 'name')],
            items: [],
            emptyMessage: 'Nothing to show here',
        );

        self::assertStringContainsString('Nothing to show here', $html);
    }

    /**
     * @param list<DataColumn>       $columns
     * @param list<array|object>     $items
     */
    private function render(
        array $columns,
        array $items,
        ?SortState $sort = null,
        string $emptyMessage = 'No records found',
    ): string {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel_test/datatable_test.html.twig', [
            'columns' => $columns,
            'items' => $items,
            'sort' => $sort,
            'emptyMessage' => $emptyMessage,
        ]);
    }
}
