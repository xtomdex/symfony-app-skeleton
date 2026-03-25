<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Exception;

use App\Modules\System\Exception\DocumentNotFoundException;
use PHPUnit\Framework\TestCase;

final class DocumentNotFoundExceptionTest extends TestCase
{
    public function test_by_id(): void
    {
        $exception = DocumentNotFoundException::byId('abc-123');

        self::assertStringContainsString('abc-123', $exception->getMessage());
    }

    public function test_by_slug(): void
    {
        $exception = DocumentNotFoundException::bySlug('my-slug');

        self::assertStringContainsString('my-slug', $exception->getMessage());
    }
}
