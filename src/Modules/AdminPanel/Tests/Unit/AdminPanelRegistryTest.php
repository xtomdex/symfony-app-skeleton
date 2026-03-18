<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit;

use App\Modules\AdminPanel\AdminPanelRegistry;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\DuplicateNamePanel;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\MainTestPanel;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\PartnerTestPanel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(AdminPanelRegistry::class)]
final class AdminPanelRegistryTest extends TestCase
{
    #[Test]
    public function resolveByRequest_returns_panel_matching_prefix(): void
    {
        $panel = new MainTestPanel();
        $registry = new AdminPanelRegistry([$panel]);

        $request = Request::create('/admin/users');

        self::assertSame($panel, $registry->resolveByRequest($request));
    }

    #[Test]
    public function resolveByRequest_returns_null_when_no_match(): void
    {
        $registry = new AdminPanelRegistry([new MainTestPanel()]);

        $request = Request::create('/api/users');

        self::assertNull($registry->resolveByRequest($request));
    }

    #[Test]
    public function resolveByRequest_matches_correct_panel_among_multiple(): void
    {
        $main = new MainTestPanel();
        $partner = new PartnerTestPanel();
        $registry = new AdminPanelRegistry([$main, $partner]);

        $request = Request::create('/partner/dashboard');

        self::assertSame($partner, $registry->resolveByRequest($request));
    }

    #[Test]
    public function resolveByRequest_matches_exact_prefix(): void
    {
        $panel = new MainTestPanel();
        $registry = new AdminPanelRegistry([$panel]);

        $request = Request::create('/admin');

        self::assertSame($panel, $registry->resolveByRequest($request));
    }

    #[Test]
    public function get_returns_panel_by_name(): void
    {
        $panel = new MainTestPanel();
        $registry = new AdminPanelRegistry([$panel]);

        self::assertSame($panel, $registry->get('main'));
    }

    #[Test]
    public function get_throws_on_unknown_name(): void
    {
        $registry = new AdminPanelRegistry([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Admin panel "unknown" not found');

        $registry->get('unknown');
    }

    #[Test]
    public function constructor_throws_on_duplicate_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate admin panel name "main"');

        new AdminPanelRegistry([new MainTestPanel(), new DuplicateNamePanel()]);
    }

    #[Test]
    public function empty_registry_resolves_null(): void
    {
        $registry = new AdminPanelRegistry([]);

        self::assertNull($registry->resolveByRequest(Request::create('/admin')));
    }
}
