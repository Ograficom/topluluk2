<?php

namespace Tests\Unit;

use App\Support\StructuredDataUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StructuredDataUrlTest extends TestCase
{
    #[DataProvider('validUrls')]
    public function test_it_normalizes_valid_same_as_urls(string $value, string $platform, string $expected): void
    {
        $this->assertSame($expected, StructuredDataUrl::sameAs($value, $platform));
    }

    public static function validUrls(): array
    {
        return [
            ['https://example.com/profile', 'website', 'https://example.com/profile'],
            ['example.com/profile', 'website', 'https://example.com/profile'],
            ['@ografi', 'instagram', 'https://www.instagram.com/ografi'],
            ['ografi', 'x', 'https://x.com/ografi'],
            ['+90 555 111 22 33', 'whatsapp', 'https://wa.me/905551112233'],
            ['0555 111 22 33', 'whatsapp', 'https://wa.me/905551112233'],
        ];
    }

    #[DataProvider('invalidUrls')]
    public function test_it_rejects_invalid_same_as_urls(string $value, string $platform): void
    {
        $this->assertNull(StructuredDataUrl::sameAs($value, $platform));
    }

    public static function invalidUrls(): array
    {
        return [
            ['', 'website'],
            ['not-a-domain', 'website'],
            ['https://localhost/profile', 'website'],
            ['https://user:pass@example.com/profile', 'website'],
            ['javascript:alert(1)', 'instagram'],
            ['bad handle', 'x'],
        ];
    }
}
