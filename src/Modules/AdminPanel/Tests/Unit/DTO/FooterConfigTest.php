<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\DTO;

use App\Modules\AdminPanel\DTO\FooterConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FooterConfig::class)]
final class FooterConfigTest extends TestCase
{
    #[Test]
    public function create_sets_text_without_url(): void
    {
        $footer = FooterConfig::create('© My Company');

        self::assertSame('© My Company', $footer->text());
        self::assertNull($footer->url());
    }

    #[Test]
    public function withUrl_returns_new_instance(): void
    {
        $original = FooterConfig::create('© My Company');
        $withUrl = $original->withUrl('https://example.com');

        self::assertNull($original->url());
        self::assertSame('https://example.com', $withUrl->url());
        self::assertNotSame($original, $withUrl);
    }
}
