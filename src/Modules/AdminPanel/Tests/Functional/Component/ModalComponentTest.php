<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Functional\Component;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class ModalComponentTest extends KernelTestCase
{
    #[Test]
    public function renders_modal_with_correct_id_attribute(): void
    {
        $html = $this->render('modal_test.html.twig');

        self::assertStringContainsString('id="testModal"', $html);
        self::assertStringContainsString('aria-labelledby="testModalLabel"', $html);
    }

    #[Test]
    public function renders_title_in_header(): void
    {
        $html = $this->render('modal_test.html.twig');

        self::assertStringContainsString('modal-title', $html);
        self::assertStringContainsString('Test Modal Title', $html);
        self::assertStringContainsString('id="testModalLabel"', $html);
    }

    #[Test]
    public function renders_close_button_when_closable_true(): void
    {
        $html = $this->render('modal_test.html.twig');

        self::assertStringContainsString('btn-close', $html);
        self::assertStringContainsString('data-bs-dismiss="modal"', $html);
    }

    #[Test]
    public function no_close_button_when_closable_false(): void
    {
        $html = $this->render('modal_no_close_test.html.twig');

        self::assertStringNotContainsString('btn-close', $html);
    }

    #[Test]
    public function no_header_rendered_when_title_empty_and_closable_false(): void
    {
        $html = $this->render('modal_no_header_test.html.twig');

        self::assertStringNotContainsString('modal-header', $html);
    }

    #[Test]
    public function size_class_applied_when_size_lg(): void
    {
        $html = $this->render('modal_size_test.html.twig');

        self::assertStringContainsString('modal-lg', $html);
    }

    #[Test]
    public function default_size_has_no_extra_size_class(): void
    {
        $html = $this->render('modal_test.html.twig');

        self::assertStringNotContainsString('modal-sm', $html);
        self::assertStringNotContainsString('modal-lg', $html);
        self::assertStringNotContainsString('modal-xl', $html);
    }

    #[Test]
    public function scrollable_true_adds_modal_dialog_scrollable(): void
    {
        $html = $this->render('modal_options_test.html.twig');

        self::assertStringContainsString('modal-dialog-scrollable', $html);
    }

    #[Test]
    public function centered_true_adds_modal_dialog_centered(): void
    {
        $html = $this->render('modal_options_test.html.twig');

        self::assertStringContainsString('modal-dialog-centered', $html);
    }

    #[Test]
    public function static_backdrop_adds_data_attributes(): void
    {
        $html = $this->render('modal_options_test.html.twig');

        self::assertStringContainsString('data-bs-backdrop="static"', $html);
        self::assertStringContainsString('data-bs-keyboard="false"', $html);
    }

    #[Test]
    public function body_slot_renders_content(): void
    {
        $html = $this->render('modal_test.html.twig');

        self::assertStringContainsString('modal-body', $html);
        self::assertStringContainsString('Modal body content', $html);
    }

    #[Test]
    public function footer_slot_renders_content(): void
    {
        $html = $this->render('modal_test.html.twig');

        self::assertStringContainsString('modal-footer', $html);
        self::assertStringContainsString('Cancel', $html);
        self::assertStringContainsString('Save', $html);
    }

    #[Test]
    public function footer_not_rendered_when_slot_empty(): void
    {
        $html = $this->render('modal_no_close_test.html.twig');

        self::assertStringNotContainsString('modal-footer', $html);
    }

    #[Test]
    public function combined_static_backdrop_and_closable_false(): void
    {
        $html = $this->render('modal_static_no_close_test.html.twig');

        self::assertStringNotContainsString('btn-close', $html);
        self::assertStringContainsString('data-bs-backdrop="static"', $html);
        self::assertStringContainsString('data-bs-keyboard="false"', $html);
    }

    private function render(string $template): string
    {
        self::bootKernel();

        /** @var Environment $twig */
        $twig = self::getContainer()->get(Environment::class);

        return $twig->render('@admin_panel_test/' . $template);
    }
}
