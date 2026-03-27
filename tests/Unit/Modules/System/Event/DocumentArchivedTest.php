<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Event;

use App\Modules\System\Event\DocumentArchived;
use PHPUnit\Framework\TestCase;

final class DocumentArchivedTest extends TestCase
{
    public function test_construction_and_field_access(): void
    {
        $event = new DocumentArchived('doc-id', 'my-slug');

        self::assertSame('doc-id', $event->documentId);
        self::assertSame('my-slug', $event->slug);
    }

    public function test_get_payload(): void
    {
        $event = new DocumentArchived('doc-id', 'my-slug');

        self::assertSame(['documentId' => 'doc-id', 'slug' => 'my-slug'], $event->getPayload());
    }
}
