<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Exception;

use App\Modules\System\Exception\DocumentSlugAlreadyExistsException;
use PHPUnit\Framework\TestCase;

final class DocumentSlugAlreadyExistsExceptionTest extends TestCase
{
    public function test_for_slug(): void
    {
        $exception = DocumentSlugAlreadyExistsException::forSlug('my-slug');

        self::assertStringContainsString('my-slug', $exception->getMessage());
    }
}
