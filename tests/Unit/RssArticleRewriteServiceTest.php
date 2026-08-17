<?php

namespace Tests\Unit;

use App\Services\Rss\RssArticleRewriteService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class RssArticleRewriteServiceTest extends TestCase
{
    #[Test]
    public function it_detects_long_verbatim_runs_inside_otherwise_different_copy(): void
    {
        $source = 'Ankara Valiligi yapilan incelemenin ardindan karari kamuoyuna resmi bir aciklamayla duyurdu.';
        $draft = 'Yeni giris. yapilan incelemenin ardindan karari kamuoyuna resmi bir aciklamayla duyurdu ve surec tamamlandi.';

        $this->assertSame(9, $this->invoke('longestSharedWordRun', $source, $draft));
    }

    #[Test]
    public function it_rejects_numbers_that_do_not_exist_in_the_source(): void
    {
        $source = 'Toplanti 17 Agustos 2026 tarihinde saat 14.30 icin planlandi.';
        $draft = '17 Agustos 2026 tarihindeki toplantiya 250 kisi katildi.';

        $this->assertSame(['250'], $this->invoke('unsupportedNumbers', $source, $draft));
    }

    private function invoke(string $method, mixed ...$arguments): mixed
    {
        $reflection = new ReflectionMethod(RssArticleRewriteService::class, $method);

        return $reflection->invoke(new RssArticleRewriteService(), ...$arguments);
    }
}
