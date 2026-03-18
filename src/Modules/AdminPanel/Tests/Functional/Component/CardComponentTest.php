<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Component;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class CardComponentTest extends KernelTestCase
{
    #[Test]
    public function renders_title_and_subtitle(): void
    {
        $html = $this->renderCard('card_test.html.twig');

        self::assertStringContainsString('Test Card', $html);
        self::assertStringContainsString('Test subtitle', $html);
    }

    #[Test]
    public function renders_card_structure(): void
    {
        $html = $this->renderCard('card_test.html.twig');

        self::assertStringContainsString('class="card"', $html);
        self::assertStringContainsString('card-header', $html);
        self::assertStringContainsString('card-body', $html);
    }

    #[Test]
    public function renders_actions_slot(): void
    {
        $html = $this->renderCard('card_test.html.twig');

        self::assertStringContainsString('btn btn-sm btn-outline-primary', $html);
        self::assertStringContainsString('Edit', $html);
    }

    #[Test]
    public function renders_body_content(): void
    {
        $html = $this->renderCard('card_test.html.twig');

        self::assertStringContainsString('Test card body', $html);
    }

    #[Test]
    public function renders_footer_slot(): void
    {
        $html = $this->renderCard('card_test.html.twig');

        self::assertStringContainsString('card-footer', $html);
        self::assertStringContainsString('Test footer content', $html);
    }

    #[Test]
    public function renders_minimal_card_without_footer(): void
    {
        $html = $this->renderCard('card_minimal_test.html.twig');

        self::assertStringContainsString('Minimal Card', $html);
        self::assertStringContainsString('Just body content', $html);
    }

    private function renderCard(string $template): string
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel_test/' . $template);
    }
}
