<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Navigation tabs component — renders a row of links as sub-navigation.
 * Not Bootstrap JS tabs. Each link navigates to a separate page/route.
 *
 * Usage:
 *     <twig:Admin:NavTabs
 *         :tabs="[
 *             {label: 'General', url: path('admin_user_edit', {id: user.id}), active: true},
 *             {label: 'Security', url: path('admin_user_security', {id: user.id})},
 *             {label: 'Activity', url: path('admin_user_activity', {id: user.id}), icon: 'tabler-history'},
 *         ]"
 *     />
 */
#[AsTwigComponent]
final class NavTabs
{
    /**
     * Tab links.
     * Each: ['label' => string, 'url' => string, 'active' => bool, 'icon' => string|null].
     *
     * @var list<array{label: string, url: string, active?: bool, icon?: string}>
     */
    public array $tabs = [];
}
