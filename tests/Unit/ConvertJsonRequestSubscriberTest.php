<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Infrastructure\EventSubscriber\ConvertJsonRequestSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ConvertJsonRequestSubscriberTest extends TestCase
{
    public function test_it_does_nothing_when_skip_flag_is_set(): void
    {
        $subscriber = new ConvertJsonRequestSubscriber();

        $request = new Request(
            query: [],
            request: [], // should stay empty
            attributes: ['skip_json_body_parsing' => true],
            cookies: [],
            files: [],
            server: [
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(['value' => 'abc'])
        );

        $kernel = $this->createStub(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->convertJsonStringToArray($event);

        // If opt-out works, request->request should remain unchanged/empty.
        $this->assertSame([], $request->request->all());
    }

    public function test_it_converts_json_body_to_request_params_when_not_skipped(): void
    {
        $subscriber = new ConvertJsonRequestSubscriber();

        $request = new Request(
            query: [],
            request: [],
            attributes: [], // not skipped
            cookies: [],
            files: [],
            server: [
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(['value' => 'abc'])
        );

        $kernel = $this->createStub(HttpKernelInterface::class);
        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);

        $subscriber->convertJsonStringToArray($event);

        $this->assertSame(['value' => 'abc'], $request->request->all());
    }
}
