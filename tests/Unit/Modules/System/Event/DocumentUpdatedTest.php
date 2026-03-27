<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Event;

use App\Modules\System\Event\DocumentUpdated;
use PHPUnit\Framework\TestCase;

final class DocumentUpdatedTest extends TestCase
{
    public function test_construction_and_field_access(): void
    {
        $event = new DocumentUpdated('doc-id');

        self::assertSame('doc-id', $event->documentId);
    }

    public function test_get_payload(): void
    {
        $event = new DocumentUpdated('doc-id');

        self::assertSame(['documentId' => 'doc-id'], $event->getPayload());
    }
}
