<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Enum;

use App\Modules\System\Enum\DocumentStatus;
use PHPUnit\Framework\TestCase;

final class DocumentStatusTest extends TestCase
{
    public function test_draft_has_correct_value(): void
    {
        self::assertSame('draft', DocumentStatus::Draft->value);
    }

    public function test_published_has_correct_value(): void
    {
        self::assertSame('published', DocumentStatus::Published->value);
    }

    public function test_archived_has_correct_value(): void
    {
        self::assertSame('archived', DocumentStatus::Archived->value);
    }

    public function test_from_draft(): void
    {
        self::assertSame(DocumentStatus::Draft, DocumentStatus::from('draft'));
    }

    public function test_from_published(): void
    {
        self::assertSame(DocumentStatus::Published, DocumentStatus::from('published'));
    }

    public function test_from_archived(): void
    {
        self::assertSame(DocumentStatus::Archived, DocumentStatus::from('archived'));
    }
}
