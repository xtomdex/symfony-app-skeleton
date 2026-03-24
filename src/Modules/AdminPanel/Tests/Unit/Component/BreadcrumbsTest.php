<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\Component;

use App\Modules\AdminPanel\Contract\AdminPanelInterface;
use App\Modules\AdminPanel\DTO\AdminUserView;
use App\Modules\AdminPanel\DTO\BrandConfig;
use App\Modules\AdminPanel\DTO\Breadcrumb;
use App\Modules\AdminPanel\DTO\FooterConfig;
use App\Modules\AdminPanel\DTO\MenuItem;
use App\Modules\AdminPanel\Twig\Component\Breadcrumbs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(Breadcrumbs::class)]
final class BreadcrumbsTest extends TestCase
{
    #[Test]
    public function showHome_true_with_panel_prepends_home_with_panel_path(): void
    {
        $panel = $this->makePanel('/test-admin');

        $request = $this->createStub(Request::class);
        $request->attributes = new ParameterBag(['_admin_panel' => $panel]);

        $requestStack = $this->createStub(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $component = new Breadcrumbs($requestStack);
        $component->showHome = true;
        $component->items = [new Breadcrumb('Users', '/admin/users')];

        $result = $component->getResolvedItems();

        self::assertCount(2, $result);
        self::assertSame('', $result[0]->label);
        self::assertSame('/test-admin', $result[0]->url);
        self::assertSame('tabler-home', $result[0]->icon);
        self::assertSame('Users', $result[1]->label);
    }

    #[Test]
    public function showHome_true_without_panel_uses_slash_as_fallback(): void
    {
        $request = $this->createStub(Request::class);
        $request->attributes = new ParameterBag([]);

        $requestStack = $this->createStub(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $component = new Breadcrumbs($requestStack);
        $component->showHome = true;
        $component->items = [];

        $result = $component->getResolvedItems();

        self::assertCount(2, $result);
        self::assertSame('/', $result[0]->url);
        self::assertSame('tabler-home', $result[0]->icon);
        self::assertSame('...', $result[1]->label);
    }

    #[Test]
    public function showHome_true_with_empty_items_returns_home_and_ellipsis_placeholder(): void
    {
        $requestStack = $this->createStub(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(null);

        $component = new Breadcrumbs($requestStack);
        $component->showHome = true;
        $component->items = [];

        $result = $component->getResolvedItems();

        self::assertCount(2, $result);
        self::assertSame('tabler-home', $result[0]->icon);
        self::assertSame('/', $result[0]->url);
        self::assertSame('...', $result[1]->label);
        self::assertNull($result[1]->url);
        self::assertNull($result[1]->icon);
    }

    #[Test]
    public function showHome_false_returns_items_unchanged(): void
    {
        $requestStack = $this->createStub(RequestStack::class);

        $component = new Breadcrumbs($requestStack);
        $component->showHome = false;
        $component->items = [
            new Breadcrumb('Section', '/section'),
            new Breadcrumb('Page', null),
        ];

        $result = $component->getResolvedItems();

        self::assertCount(2, $result);
        self::assertSame('Section', $result[0]->label);
        self::assertSame('Page', $result[1]->label);
    }

    #[Test]
    public function showHome_false_with_empty_items_returns_empty_array(): void
    {
        $requestStack = $this->createStub(RequestStack::class);

        $component = new Breadcrumbs($requestStack);
        $component->showHome = false;
        $component->items = [];

        $result = $component->getResolvedItems();

        self::assertSame([], $result);
    }

    private function makePanel(string $homePath): AdminPanelInterface
    {
        return new class($homePath) implements AdminPanelInterface {
            public function __construct(private readonly string $path) {}

            public static function name(): string { return 'test'; }
            public static function routePrefix(): string { return '/test'; }
            public function brand(): BrandConfig { return BrandConfig::create('Test'); }
            /** @return list<MenuItem> */
            public function menuItems(): array { return []; }
            public function userView(): ?AdminUserView { return null; }
            public function footer(): FooterConfig { return FooterConfig::create('Test'); }
            public function homePath(): string { return $this->path; }
        };
    }
}
