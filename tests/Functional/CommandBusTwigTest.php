<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommandBusTwigTest extends WebTestCase
{
    public function test_twig_render_only_renders_template_and_has_no_result(): void
    {
        $client = static::createClient();
        $client->request('GET', '/_test/twig/render-only');

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $content = (string) $response->getContent();
        $this->assertStringContainsString('OK:', $content);
        $this->assertStringContainsString('NO_RESULT', $content);
    }

    public function test_twig_invalid_form_does_not_call_handler_and_renders_no_result(): void
    {
        $client = static::createClient();

        // Submit invalid payload
        $client->request('POST', '/_test/twig/invalid-form', [
            'value' => '',
        ]);

        // If handler was called, it would throw and response would be 500.
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $content = (string) $response->getContent();
        $this->assertStringContainsString('FORM:', $content);
        $this->assertStringContainsString('NO_RESULT', $content);
    }

    public function test_twig_valid_form_calls_handler_and_renders_has_result(): void
    {
        $client = static::createClient();

        $client->request('POST', '/_test/twig/valid-form', [
            'value' => 'hello',
        ]);

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $content = (string) $response->getContent();
        $this->assertStringContainsString('FORM:', $content);
        $this->assertStringContainsString('HAS_RESULT', $content);
    }
}
