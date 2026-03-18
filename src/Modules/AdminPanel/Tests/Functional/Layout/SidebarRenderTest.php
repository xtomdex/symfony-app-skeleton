<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Layout;

use App\Modules\AdminPanel\Listener\ResolveAdminPanelListener;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\MainTestPanel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

final class SidebarRenderTest extends KernelTestCase
{
    #[Test]
    public function sidebar_renders_brand_name(): void
    {
        $html = $this->renderTemplate('@admin_panel/layout/_sidebar.html.twig');

        self::assertStringContainsString('Test App', $html);
    }

    #[Test]
    public function sidebar_renders_brand_logo(): void
    {
        $html = $this->renderTemplate('@admin_panel/layout/_sidebar.html.twig');

        self::assertStringContainsString('img/test-logo.svg', $html);
    }

    #[Test]
    public function sidebar_renders_section_header(): void
    {
        $html = $this->renderTemplate('@admin_panel/layout/_sidebar.html.twig');

        self::assertStringContainsString('Management', $html);
    }

    #[Test]
    public function sidebar_renders_menu_items_with_icons(): void
    {
        $html = $this->renderTemplate('@admin_panel/layout/_sidebar.html.twig');

        self::assertStringContainsString('tabler-dashboard', $html);
        self::assertStringContainsString('tabler-users', $html);
    }

    #[Test]
    public function sidebar_renders_menu_links(): void
    {
        $html = $this->renderTemplate('@admin_panel/layout/_sidebar.html.twig');

        // Menu builder resolves routes — in test env they resolve to stubs
        self::assertStringContainsString('menu-link', $html);
    }

    private function renderTemplate(string $template): string
    {
        self::bootKernel();

        $this->pushAdminRequest();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render($template);
    }

    private function pushAdminRequest(): void
    {
        $request = Request::create('/admin/dashboard');
        $request->attributes->set('_route', 'test_admin_dashboard');
        $request->attributes->set(
            ResolveAdminPanelListener::REQUEST_ATTRIBUTE,
            self::getContainer()->get(MainTestPanel::class),
        );

        /** @var RequestStack $requestStack */
        $requestStack = self::getContainer()->get(RequestStack::class);
        $requestStack->push($request);
    }
}
