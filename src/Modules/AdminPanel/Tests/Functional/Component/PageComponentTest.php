<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Component;

use App\Modules\AdminPanel\DTO\Breadcrumb;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class PageComponentTest extends KernelTestCase
{
    #[Test]
    public function renders_title(): void
    {
        $html = $this->renderPage();

        self::assertStringContainsString('Test Page', $html);
    }

    #[Test]
    public function renders_breadcrumbs(): void
    {
        $html = $this->renderPage();

        self::assertStringContainsString('Users', $html);
        self::assertStringContainsString('aria-current="page"', $html);
    }

    #[Test]
    public function renders_home_icon_when_showHome_true(): void
    {
        $html = $this->renderPage();

        self::assertStringContainsString('tabler-home', $html);
    }

    #[Test]
    public function renders_actions_slot(): void
    {
        $html = $this->renderPage();

        self::assertStringContainsString('Test Action', $html);
        self::assertStringContainsString('btn btn-primary', $html);
    }

    #[Test]
    public function renders_content(): void
    {
        $html = $this->renderPage();

        self::assertStringContainsString('Test page content', $html);
    }

    #[Test]
    public function renders_home_icon_when_no_breadcrumbs(): void
    {
        $html = $this->renderPageWithoutBreadcrumbs();

        self::assertStringContainsString('tabler-home', $html);
    }

    private function renderPage(): string
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel_test/page_test.html.twig', [
            'breadcrumbs' => [
                new Breadcrumb(label: 'Users', url: '/admin/users'),
            ],
        ]);
    }

    private function renderPageWithoutBreadcrumbs(): string
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel_test/page_test.html.twig', [
            'breadcrumbs' => [],
        ]);
    }
}
