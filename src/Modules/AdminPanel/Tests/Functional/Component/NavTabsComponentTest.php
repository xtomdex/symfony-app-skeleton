<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Component;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class NavTabsComponentTest extends KernelTestCase
{
    #[Test]
    public function renders_tab_links(): void
    {
        $html = $this->renderTabs();

        self::assertStringContainsString('General', $html);
        self::assertStringContainsString('Security', $html);
        self::assertStringContainsString('Activity', $html);
    }

    #[Test]
    public function renders_urls_as_hrefs(): void
    {
        $html = $this->renderTabs();

        self::assertStringContainsString('href="/admin/users/1/edit"', $html);
        self::assertStringContainsString('href="/admin/users/1/security"', $html);
        self::assertStringContainsString('href="/admin/users/1/activity"', $html);
    }

    #[Test]
    public function marks_active_tab(): void
    {
        $html = $this->renderTabs();

        // General is active — should have 'active' class and aria-current
        self::assertStringContainsString('aria-current="page"', $html);
    }

    #[Test]
    public function renders_icon_when_provided(): void
    {
        $html = $this->renderTabs();

        self::assertStringContainsString('tabler-history', $html);
    }

    #[Test]
    public function renders_nav_structure(): void
    {
        $html = $this->renderTabs();

        self::assertStringContainsString('nav nav-tabs', $html);
        self::assertStringContainsString('nav-item', $html);
        self::assertStringContainsString('nav-link', $html);
    }

    private function renderTabs(): string
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel_test/navtabs_test.html.twig', [
            'tabs' => [
                ['label' => 'General', 'url' => '/admin/users/1/edit', 'active' => true],
                ['label' => 'Security', 'url' => '/admin/users/1/security'],
                ['label' => 'Activity', 'url' => '/admin/users/1/activity', 'icon' => 'tabler-history'],
            ],
        ]);
    }
}
