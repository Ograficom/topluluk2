<?php

namespace Tests\Unit;

use App\Services\Rss\RssSyncService;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class RssMediaFilterTest extends TestCase
{
    #[DataProvider('nonArticleImageUrls')]
    public function test_it_rejects_non_article_image_urls(string $url): void
    {
        $method = new ReflectionMethod(RssSyncService::class, 'looksLikeSiteAsset');

        $this->assertTrue($method->invoke(app(RssSyncService::class), $url));
    }

    public static function nonArticleImageUrls(): array
    {
        return [
            'subscription banner' => ['https://example.com/images/e-dergi-aboneligi-banner.jpg'],
            'google source card' => ['https://example.com/assets/google-kaynak-olarak-ekle.png'],
            'advertising size' => ['https://example.com/uploads/creative-728x90.webp'],
            'sponsored creative' => ['https://example.com/media/sponsored-promo.jpg'],
        ];
    }

    public function test_it_keeps_normal_article_images(): void
    {
        $method = new ReflectionMethod(RssSyncService::class, 'looksLikeSiteAsset');

        $this->assertFalse($method->invoke(
            app(RssSyncService::class),
            'https://example.com/uploads/2026/07/meclis-toplantisi-haber-fotografi.jpg'
        ));
    }
}
