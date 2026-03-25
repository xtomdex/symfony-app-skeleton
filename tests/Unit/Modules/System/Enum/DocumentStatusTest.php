<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Enum;

use App\Modules\System\Enum\DocumentStatus;
use PHPUnit\Framework\TestCase;

final class DocumentStatusTest extends TestCase
{
    public function test_draft_has_correct_value(): void
    {
        self::assertSame('draft', DocumentStatus::DRAFT->value);
    }

    public function test_published_has_correct_value(): void
    {
        self::assertSame('published', DocumentStatus::PUBLISHED->value);
    }

    public function test_archived_has_correct_value(): void
    {
        self::assertSame('archived', DocumentStatus::ARCHIVED->value);
    }

    public function test_from_draft(): void
    {
        self::assertSame(DocumentStatus::DRAFT, DocumentStatus::from('draft'));
    }

    public function test_from_published(): void
    {
        self::assertSame(DocumentStatus::PUBLISHED, DocumentStatus::from('published'));
    }

    public function test_from_archived(): void
    {
        self::assertSame(DocumentStatus::ARCHIVED, DocumentStatus::from('archived'));
    }
}
