<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Fixtures;

use App\Modules\AdminPanel\Contract\AdminPanelInterface;
use App\Modules\AdminPanel\DTO\AdminUserView;
use App\Modules\AdminPanel\DTO\BrandConfig;
use App\Modules\AdminPanel\DTO\FooterConfig;

/**
 * Has same name as MainTestPanel ('main') but different prefix.
 * Used to test duplicate name detection.
 */
final class DuplicateNamePanel implements AdminPanelInterface
{
    public static function name(): string { return 'main'; }
    public static function routePrefix(): string { return '/other'; }
    public function brand(): BrandConfig { return BrandConfig::create('Dup'); }
    public function menuItems(): array { return []; }
    public function userView(): ?AdminUserView { return null; }
    public function footer(): FooterConfig { return FooterConfig::create('Dup'); }
}
