<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\Enum\ResponseErrorCode;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommandBusApiTest extends WebTestCase
{
    public function test_api_success_returns_envelope(): void
    {
        $client = static::createClient();
        $client->request('POST', '/_test/api/success');

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertTrue($data['ok']);
        $this->assertSame(200, $data['status_code']);
        $this->assertNull($data['error_code']);
        $this->assertNull($data['error_message']);
    }

    public function test_api_validation_submitted_but_invalid_contains_field_error(): void
    {
        $client = static::createClient();

        // JSON body should be converted to Request::request by ConvertJsonRequestSubscriber.
        $client->request(
            'POST',
            '/_test/api/validation',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['value' => ''])
        );

        $response = $client->getResponse();
        $this->assertSame(400, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true);

        $this->assertFalse($data['ok']);
        $this->assertSame(400, $data['status_code']);
        $this->assertSame(ResponseErrorCode::Validation->value, $data['error_code']);

        $this->assertIsArray($data['errors']);
        $this->assertArrayHasKey('value', $data['errors']);
        $this->assertNotEmpty($data['errors']['value']);

        // More stable than exact equality.
        $this->assertStringContainsString('blank', strtolower((string) $data['errors']['value']));
    }

    public function test_api_returns_internal_error_code_on_unhandled_throwable(): void
    {
        $client = static::createClient();
        $client->request('POST', '/_test/api/throwable');

        $response = $client->getResponse();
        $this->assertSame(500, $response->getStatusCode());

        $data = json_decode((string) $response->getContent(), true);

        $this->assertFalse($data['ok']);
        $this->assertSame(500, $data['status_code']);
        $this->assertSame(ResponseErrorCode::Internal->value, $data['error_code']);
        $this->assertNotEmpty($data['error_message']);
    }
}
