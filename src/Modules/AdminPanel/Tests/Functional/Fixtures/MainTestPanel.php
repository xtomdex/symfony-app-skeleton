<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Fixtures;

use App\Modules\AdminPanel\Contract\AdminPanelInterface;
use App\Modules\AdminPanel\DTO\AdminUserView;
use App\Modules\AdminPanel\DTO\BrandConfig;
use App\Modules\AdminPanel\DTO\FooterConfig;
use App\Modules\AdminPanel\DTO\MenuItem;

final class MainTestPanel implements AdminPanelInterface
{
    public static function name(): string
    {
        return 'main';
    }

    public static function routePrefix(): string
    {
        return '/admin';
    }

    public function brand(): BrandConfig
    {
        return BrandConfig::create('Test App')
            ->withLogos('img/test-logo.svg');
    }

    public function menuItems(): array
    {
        return [
            MenuItem::section('Management'),
            MenuItem::linkToUrl('Dashboard', '/admin/dashboard')
                ->withIcon('tabler-dashboard'),
            MenuItem::linkToUrl('Users', '/admin/users')
                ->withIcon('tabler-users')
                ->withRoutePrefix('test_admin_user'),
        ];
    }

    public function userView(): ?AdminUserView
    {
        return new AdminUserView(
            displayName: 'Test Admin',
            avatarUrl: null,
            role: 'Admin',
            profileUrl: '/admin/profile',
            logoutUrl: '/admin/logout',
        );
    }

    public function footer(): FooterConfig
    {
        return FooterConfig::create('© Test Company');
    }
}
