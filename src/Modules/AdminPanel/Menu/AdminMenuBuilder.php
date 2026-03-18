<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Menu;

use App\Modules\AdminPanel\DTO\MenuItem;
use App\Modules\AdminPanel\DTO\PreparedMenuItem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class AdminMenuBuilder
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack,
    ) {}

    /**
     * @param list<MenuItem> $items
     * @return list<PreparedMenuItem>
     */
    public function build(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            if ($item->isSection()) {
                $result[] = PreparedMenuItem::section($item->label());
                continue;
            }

            $children = $item->hasChildren() ? $this->build($item->children()) : [];

            if ($item->hasChildren() && count($children) === 0) {
                continue;
            }

            $link = $this->resolveLink($item);
            $active = $this->isActive($item);
            $open = $active;

            foreach ($children as $child) {
                if ($child->active || $child->open) {
                    $open = true;
                    break;
                }
            }

            $result[] = new PreparedMenuItem(
                label: $item->label(),
                icon: $item->icon(),
                link: $link,
                active: $active,
                open: $open,
                children: $children,
            );
        }

        return $result;
    }

    private function resolveLink(MenuItem $item): ?string
    {
        if ($item->url() !== null) {
            return $item->url();
        }

        if ($item->route() !== null) {
            return $this->urlGenerator->generate($item->route(), $item->routeParams());
        }

        return null;
    }

    private function isActive(MenuItem $item): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return false;
        }

        $value = $request->attributes->get('_route');
        $currentRoute = \is_string($value) ? $value : null;

        if ($currentRoute === null) {
            return false;
        }

        if ($item->route() !== null && $item->route() === $currentRoute) {
            return true;
        }

        $prefix = $item->routePrefix();

        return $prefix !== null && str_starts_with($currentRoute, $prefix);
    }
}
