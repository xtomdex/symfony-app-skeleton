<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\Listener;

use App\Modules\AdminPanel\AdminPanelRegistry;
use App\Modules\AdminPanel\Listener\ResolveAdminPanelListener;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\MainTestPanel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(ResolveAdminPanelListener::class)]
final class ResolveAdminPanelListenerTest extends TestCase
{
    private AdminPanelRegistry $registry;
    private ResolveAdminPanelListener $listener;

    protected function setUp(): void
    {
        $this->registry = new AdminPanelRegistry([new MainTestPanel()]);
        $this->listener = new ResolveAdminPanelListener($this->registry);
    }

    #[Test]
    public function sets_panel_attribute_on_matching_request(): void
    {
        $request = Request::create('/admin/dashboard');
        $event = $this->createMainRequestEvent($request);

        ($this->listener)($event);

        self::assertTrue($request->attributes->has(ResolveAdminPanelListener::REQUEST_ATTRIBUTE));
        self::assertInstanceOf(
            MainTestPanel::class,
            $request->attributes->get(ResolveAdminPanelListener::REQUEST_ATTRIBUTE),
        );
    }

    #[Test]
    public function does_not_set_attribute_when_no_match(): void
    {
        $request = Request::create('/api/users');
        $event = $this->createMainRequestEvent($request);

        ($this->listener)($event);

        self::assertFalse($request->attributes->has(ResolveAdminPanelListener::REQUEST_ATTRIBUTE));
    }

    #[Test]
    public function skips_sub_requests(): void
    {
        $request = Request::create('/admin/dashboard');
        $event = $this->createSubRequestEvent($request);

        ($this->listener)($event);

        self::assertFalse($request->attributes->has(ResolveAdminPanelListener::REQUEST_ATTRIBUTE));
    }

    private function createMainRequestEvent(Request $request): RequestEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);

        return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }

    private function createSubRequestEvent(Request $request): RequestEvent
    {
        $kernel = $this->createStub(HttpKernelInterface::class);

        return new RequestEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);
    }
}
