<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Event;

use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Event\DocumentCreated;
use PHPUnit\Framework\TestCase;

final class DocumentCreatedTest extends TestCase
{
    public function test_construction_and_field_access(): void
    {
        $event = new DocumentCreated('doc-id', 'my-slug', DocumentType::Legal);

        self::assertSame('doc-id', $event->documentId);
        self::assertSame('my-slug', $event->slug);
        self::assertSame(DocumentType::Legal, $event->type);
    }

    public function test_get_payload(): void
    {
        $event = new DocumentCreated('doc-id', 'my-slug', DocumentType::Legal);

        $payload = $event->getPayload();

        self::assertSame('doc-id', $payload['documentId']);
        self::assertSame('my-slug', $payload['slug']);
        self::assertSame('legal', $payload['type']);
    }
}
