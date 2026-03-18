<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\Twig\Extension;

use App\Modules\AdminPanel\Listener\ResolveAdminPanelListener;
use App\Modules\AdminPanel\Menu\AdminMenuBuilder;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\MainTestPanel;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\PartnerTestPanel;
use App\Modules\AdminPanel\Twig\Extension\AdminUiTwigExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(AdminUiTwigExtension::class)]
final class AdminUiTwigExtensionTest extends TestCase
{
    private RequestStack $requestStack;
    private AdminUiTwigExtension $extension;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/test');

        $menuBuilder = new AdminMenuBuilder($urlGenerator, $this->requestStack);
        $this->extension = new AdminUiTwigExtension($this->requestStack, $menuBuilder);
    }

    #[Test]
    public function registers_four_twig_functions(): void
    {
        $functions = $this->extension->getFunctions();
        $names = array_map(fn ($f) => $f->getName(), $functions);

        self::assertContains('admin_brand', $names);
        self::assertContains('admin_sidebar_menu', $names);
        self::assertContains('admin_user', $names);
        self::assertContains('admin_footer', $names);
    }

    #[Test]
    public function admin_brand_returns_brand_from_active_panel(): void
    {
        $this->pushPanelRequest(new MainTestPanel());

        $brand = $this->callTwigFunction('admin_brand');

        self::assertSame('Test App', $brand->name());
    }

    #[Test]
    public function admin_sidebar_menu_returns_prepared_items(): void
    {
        $this->pushPanelRequest(new MainTestPanel());

        $menu = $this->callTwigFunction('admin_sidebar_menu');

        self::assertIsArray($menu);
        self::assertNotEmpty($menu);
        // First item is a section
        self::assertTrue($menu[0]->section);
        self::assertSame('Management', $menu[0]->label);
    }

    #[Test]
    public function admin_user_returns_user_view(): void
    {
        $this->pushPanelRequest(new MainTestPanel());

        $user = $this->callTwigFunction('admin_user');

        self::assertNotNull($user);
        self::assertSame('Test Admin', $user->displayName);
    }

    #[Test]
    public function admin_user_returns_null_when_no_user(): void
    {
        $this->pushPanelRequest(new PartnerTestPanel());

        $user = $this->callTwigFunction('admin_user');

        self::assertNull($user);
    }

    #[Test]
    public function admin_footer_returns_footer_config(): void
    {
        $this->pushPanelRequest(new MainTestPanel());

        $footer = $this->callTwigFunction('admin_footer');

        self::assertSame('© Test Company', $footer->text());
    }

    #[Test]
    public function throws_when_no_panel_in_request(): void
    {
        $this->requestStack->push(Request::create('/somewhere'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No admin panel resolved for this request');

        $this->callTwigFunction('admin_brand');
    }

    #[Test]
    public function throws_when_no_request(): void
    {
        // RequestStack is empty — no current request
        $this->expectException(\LogicException::class);

        $this->callTwigFunction('admin_brand');
    }

    private function pushPanelRequest(object $panel): void
    {
        $request = Request::create('/test');
        $request->attributes->set(ResolveAdminPanelListener::REQUEST_ATTRIBUTE, $panel);
        $this->requestStack->push($request);
    }

    private function callTwigFunction(string $name): mixed
    {
        foreach ($this->extension->getFunctions() as $function) {
            if ($function->getName() === $name) {
                return ($function->getCallable())();
            }
        }

        throw new \RuntimeException("Twig function '{$name}' not found.");
    }
}
