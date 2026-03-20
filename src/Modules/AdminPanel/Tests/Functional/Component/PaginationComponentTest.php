<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Component;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class PaginationComponentTest extends KernelTestCase
{
    #[Test]
    public function renders_page_numbers(): void
    {
        $html = $this->render(currentPage: 3, totalPages: 5);

        self::assertStringContainsString('page=1">', $html);
        self::assertStringContainsString('page=3">', $html);
        self::assertStringContainsString('page=5">', $html);
    }

    #[Test]
    public function current_page_has_active_class(): void
    {
        $html = $this->render(currentPage: 2, totalPages: 5);

        self::assertStringContainsString('page-item active', $html);
    }

    #[Test]
    public function previous_is_disabled_on_first_page(): void
    {
        $html = $this->render(currentPage: 1, totalPages: 5);

        // First page-item should be disabled
        self::assertMatchesRegularExpression('/page-item\s+disabled/', $html);
    }

    #[Test]
    public function next_is_disabled_on_last_page(): void
    {
        $html = $this->render(currentPage: 5, totalPages: 5);

        self::assertMatchesRegularExpression('/page-item\s+disabled/', $html);
    }

    #[Test]
    public function page_size_selector_renders_options(): void
    {
        $html = $this->render(currentPage: 1, totalPages: 3, pageSize: 20, pageSizeOptions: [10, 20, 50]);

        self::assertStringContainsString('pageSize=10', $html);
        self::assertStringContainsString('pageSize=20', $html);
        self::assertStringContainsString('pageSize=50', $html);
    }

    #[Test]
    public function current_page_size_is_selected(): void
    {
        $html = $this->render(currentPage: 1, totalPages: 3, pageSize: 50, pageSizeOptions: [10, 20, 50]);

        // The option for size=50 should be marked selected
        self::assertMatchesRegularExpression('/pageSize=50[^"]*"\s+selected/', $html);
    }

    #[Test]
    public function not_rendered_when_total_pages_1_and_single_page_size_option(): void
    {
        $html = $this->render(currentPage: 1, totalPages: 1, pageSize: 20, pageSizeOptions: [20]);

        self::assertStringNotContainsString('pagination', $html);
        self::assertStringNotContainsString('page-item', $html);
        self::assertStringNotContainsString('form-select', $html);
    }

    private function render(
        int $currentPage,
        int $totalPages,
        int $pageSize = 20,
        array $pageSizeOptions = [10, 20, 50, 100],
    ): string {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel_test/pagination_test.html.twig', [
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'pageSize' => $pageSize,
            'pageSizeOptions' => $pageSizeOptions,
        ]);
    }
}
