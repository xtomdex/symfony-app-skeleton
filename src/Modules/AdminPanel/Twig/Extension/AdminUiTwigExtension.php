<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Extension;

use App\Modules\AdminPanel\Contract\AdminPanelInterface;
use App\Modules\AdminPanel\DTO\AdminUserView;
use App\Modules\AdminPanel\DTO\BrandConfig;
use App\Modules\AdminPanel\DTO\FooterConfig;
use App\Modules\AdminPanel\DTO\PreparedMenuItem;
use App\Modules\AdminPanel\Listener\ResolveAdminPanelListener;
use App\Modules\AdminPanel\Menu\AdminMenuBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AdminUiTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AdminMenuBuilder $menuBuilder,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_brand', $this->brand(...)),
            new TwigFunction('admin_sidebar_menu', $this->sidebarMenu(...)),
            new TwigFunction('admin_user', $this->user(...)),
            new TwigFunction('admin_footer', $this->footer(...)),
        ];
    }

    private function brand(): BrandConfig
    {
        return $this->panel()->brand();
    }

    /**
     * @return list<PreparedMenuItem>
     */
    private function sidebarMenu(): array
    {
        return $this->menuBuilder->build($this->panel()->menuItems());
    }

    private function user(): ?AdminUserView
    {
        return $this->panel()->userView();
    }

    private function footer(): FooterConfig
    {
        return $this->panel()->footer();
    }

    private function panel(): AdminPanelInterface
    {
        $panel = $this->requestStack->getCurrentRequest()
            ?->attributes->get(ResolveAdminPanelListener::REQUEST_ATTRIBUTE);

        if (!$panel instanceof AdminPanelInterface) {
            throw new \LogicException(
                'No admin panel resolved for this request. '
                . 'Ensure the route is under a registered panel route prefix '
                . 'and ResolveAdminPanelListener is active.',
            );
        }

        return $panel;
    }
}
