<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Contract;

use App\Modules\AdminPanel\DTO\AdminUserView;
use App\Modules\AdminPanel\DTO\BrandConfig;
use App\Modules\AdminPanel\DTO\FooterConfig;
use App\Modules\AdminPanel\DTO\MenuItem;

interface AdminPanelInterface
{
    /**
     * Unique panel identifier (e.g. 'main', 'partner').
     */
    public static function name(): string;

    /**
     * URL prefix for panel resolution (e.g. '/admin', '/partner').
     * Used by ResolveAdminPanelListener to match incoming requests.
     */
    public static function routePrefix(): string;

    /**
     * Logo and application name for sidebar brand block.
     */
    public function brand(): BrandConfig;

    /**
     * Sidebar menu structure.
     *
     * @return list<MenuItem>
     */
    public function menuItems(): array;

    /**
     * Current user data for navbar dropdown.
     * Returns null when the user is not authenticated.
     */
    public function userView(): ?AdminUserView;

    /**
     * Footer content.
     */
    public function footer(): FooterConfig;
}
