<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Layout;

use App\Modules\AdminPanel\Listener\ResolveAdminPanelListener;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\MainTestPanel;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\PartnerTestPanel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

final class NavbarRenderTest extends KernelTestCase
{
    #[Test]
    public function navbar_renders_user_dropdown_when_user_present(): void
    {
        $html = $this->renderNavbar(new MainTestPanel());

        self::assertStringContainsString('Test Admin', $html);
        self::assertStringContainsString('Admin', $html);
        self::assertStringContainsString('/admin/profile', $html);
        self::assertStringContainsString('/admin/logout', $html);
    }

    #[Test]
    public function navbar_renders_avatar_initial_when_no_avatar_url(): void
    {
        $html = $this->renderNavbar(new MainTestPanel());

        // MainTestPanel has avatarUrl = null, so should show initial "T" (from "Test Admin")
        self::assertStringContainsString('avatar-initial', $html);
    }

    #[Test]
    public function navbar_hides_user_dropdown_when_no_user(): void
    {
        $html = $this->renderNavbar(new PartnerTestPanel());

        // PartnerTestPanel returns null from userView()
        self::assertStringNotContainsString('dropdown-user', $html);
        self::assertStringNotContainsString('avatar', $html);
    }

    private function renderNavbar(object $panel): string
    {
        self::bootKernel();

        $request = Request::create('/admin');
        $request->attributes->set(ResolveAdminPanelListener::REQUEST_ATTRIBUTE, $panel);

        /** @var RequestStack $requestStack */
        $requestStack = self::getContainer()->get(RequestStack::class);
        $requestStack->push($request);

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel/layout/_navbar.html.twig');
    }
}
