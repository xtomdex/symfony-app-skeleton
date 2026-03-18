<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Listener;

use App\Modules\AdminPanel\AdminPanelRegistry;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Resolves the active AdminPanel for the current request based on route prefix.
 * Stores the resolved panel as a request attribute '_admin_panel'.
 */
#[AsEventListener(event: RequestEvent::class, priority: 16)]
final readonly class ResolveAdminPanelListener
{
    public const string REQUEST_ATTRIBUTE = '_admin_panel';

    public function __construct(
        private AdminPanelRegistry $registry,
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $panel = $this->registry->resolveByRequest($event->getRequest());

        if ($panel !== null) {
            $event->getRequest()->attributes->set(self::REQUEST_ATTRIBUTE, $panel);
        }
    }
}
