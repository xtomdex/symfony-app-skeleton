<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Event;

use App\Modules\System\Event\DocumentPublished;
use PHPUnit\Framework\TestCase;

final class DocumentPublishedTest extends TestCase
{
    public function test_construction_and_field_access(): void
    {
        $event = new DocumentPublished('doc-id', 'my-slug');

        self::assertSame('doc-id', $event->documentId);
        self::assertSame('my-slug', $event->slug);
    }

    public function test_get_payload(): void
    {
        $event = new DocumentPublished('doc-id', 'my-slug');

        self::assertSame(['documentId' => 'doc-id', 'slug' => 'my-slug'], $event->getPayload());
    }
}
