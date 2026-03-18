<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Layout;

use App\Modules\AdminPanel\Listener\ResolveAdminPanelListener;
use App\Modules\AdminPanel\Tests\Functional\Fixtures\MainTestPanel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

final class FooterRenderTest extends KernelTestCase
{
    #[Test]
    public function footer_renders_text(): void
    {
        $html = $this->renderFooter();

        self::assertStringContainsString('© Test Company', $html);
    }

    #[Test]
    public function footer_renders_without_link_when_no_url(): void
    {
        $html = $this->renderFooter();

        // MainTestPanel footer has no URL — text should not be wrapped in <a>
        self::assertStringNotContainsString('footer-link', $html);
    }

    private function renderFooter(): string
    {
        self::bootKernel();

        $request = Request::create('/admin');
        $request->attributes->set(
            ResolveAdminPanelListener::REQUEST_ATTRIBUTE,
            new MainTestPanel(),
        );

        /** @var RequestStack $requestStack */
        $requestStack = self::getContainer()->get(RequestStack::class);
        $requestStack->push($request);

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel/layout/_footer.html.twig');
    }
}
