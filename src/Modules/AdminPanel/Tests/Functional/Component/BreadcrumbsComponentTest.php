<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Component;

use App\Modules\AdminPanel\DTO\Breadcrumb;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class BreadcrumbsComponentTest extends KernelTestCase
{
    #[Test]
    public function renders_home_icon_with_link_when_showHome_true(): void
    {
        $html = $this->render(
            items: [new Breadcrumb('Users', '/admin/users')],
            showHome: true,
        );

        self::assertStringContainsString('tabler-home', $html);
        self::assertStringContainsString('href="/', $html);
    }

    #[Test]
    public function renders_breadcrumb_items_with_correct_links(): void
    {
        $html = $this->render(
            items: [
                new Breadcrumb('Section', '/admin/section'),
                new Breadcrumb('Page', '/admin/section/page'),
            ],
            showHome: false,
        );

        self::assertStringContainsString('Section', $html);
        self::assertStringContainsString('href="/admin/section"', $html);
        self::assertStringContainsString('Page', $html);
    }

    #[Test]
    public function last_breadcrumb_has_aria_current_page(): void
    {
        $html = $this->render(
            items: [new Breadcrumb('Users', '/admin/users')],
            showHome: true,
        );

        // 'Users' is last (not first — home is first), so it gets aria-current
        self::assertStringContainsString('aria-current="page"', $html);
        self::assertStringContainsString('Users', $html);
    }

    #[Test]
    public function empty_items_with_showHome_true_renders_home_and_ellipsis(): void
    {
        $html = $this->render(items: [], showHome: true);

        self::assertStringContainsString('tabler-home', $html);
        // Ellipsis placeholder is appended as the active last item
        self::assertStringContainsString('...', $html);
        self::assertStringContainsString('aria-current="page"', $html);
    }

    #[Test]
    public function showHome_false_does_not_render_home_icon(): void
    {
        $html = $this->render(
            items: [new Breadcrumb('Dashboard', '/admin')],
            showHome: false,
        );

        self::assertStringNotContainsString('tabler-home', $html);
        self::assertStringContainsString('Dashboard', $html);
    }

    /**
     * @param list<Breadcrumb> $items
     */
    private function render(array $items, bool $showHome): string
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel_test/breadcrumbs_test.html.twig', [
            'items' => $items,
            'showHome' => $showHome,
        ]);
    }
}
