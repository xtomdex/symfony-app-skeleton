<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Twig\Component;

use App\Modules\AdminPanel\Contract\AdminPanelInterface;
use App\Modules\AdminPanel\DTO\Breadcrumb;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Breadcrumbs
{
    /** @var list<Breadcrumb> */
    public array $items = [];

    /** Whether to prepend a home breadcrumb with house icon */
    public bool $showHome = true;

    public function __construct(private readonly RequestStack $requestStack) {}

    /** @return list<Breadcrumb> */
    public function getResolvedItems(): array
    {
        $originalItems = $this->items;
        $result = $this->items;

        if ($this->showHome) {
            $panel = $this->requestStack->getCurrentRequest()
                ?->attributes->get('_admin_panel');

            $homeUrl = $panel instanceof AdminPanelInterface ? $panel->homePath() : '/';
            array_unshift($result, new Breadcrumb(label: '', url: $homeUrl, icon: 'tabler-home'));

            if (empty($originalItems)) {
                $result[] = new Breadcrumb(label: '...');
            }
        }

        return $result;
    }
}
