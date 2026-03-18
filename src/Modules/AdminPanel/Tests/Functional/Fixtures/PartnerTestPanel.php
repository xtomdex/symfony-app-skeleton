<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Fixtures;

use App\Modules\AdminPanel\Contract\AdminPanelInterface;
use App\Modules\AdminPanel\DTO\AdminUserView;
use App\Modules\AdminPanel\DTO\BrandConfig;
use App\Modules\AdminPanel\DTO\FooterConfig;
use App\Modules\AdminPanel\DTO\MenuItem;

final class PartnerTestPanel implements AdminPanelInterface
{
    public static function name(): string
    {
        return 'partner';
    }

    public static function routePrefix(): string
    {
        return '/partner';
    }

    public function brand(): BrandConfig
    {
        return BrandConfig::create('Partner Portal');
    }

    public function menuItems(): array
    {
        return [
            MenuItem::linkToUrl('Dashboard', '/partner/dashboard')
                ->withIcon('tabler-home'),
        ];
    }

    public function userView(): ?AdminUserView
    {
        return null;
    }

    public function footer(): FooterConfig
    {
        return FooterConfig::create('© Partner Company');
    }
}
