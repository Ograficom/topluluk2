<?php

namespace Tests\Unit;

use App\Services\Rss\RssArticleRewriteService;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class RssArticleRewriteServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        $container->instance('config', new Repository([
            'services' => [
                'ollama' => [
                    'model' => 'gpt-oss:20b',
                ],
            ],
        ]));
        Container::setInstance($container);
    }

    protected function tearDown(): void
    {
        Container::setInstance(null);
        parent::tearDown();
    }

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

    #[Test]
    public function it_accepts_json_wrapped_in_markdown_fences(): void
    {
        $raw = "```json\n{\"title\":\"Yeni baslik\",\"summary\":\"Yeni ozet\",\"content_html\":\"<p>Yeni icerik</p>\",\"tags\":[\"a\",\"b\",\"c\"]}\n```";

        $decoded = $this->invoke('decodeStructuredJson', $raw);

        $this->assertIsArray($decoded);
        $this->assertSame('Yeni baslik', $decoded['title']);
        $this->assertSame(['a', 'b', 'c'], $decoded['tags']);
    }

    #[Test]
    public function it_does_not_send_an_openai_model_name_to_ollama(): void
    {
        $this->assertSame('gpt-oss:20b', $this->invoke('normalizeOllamaModel', 'gpt-5.6-luna'));
        $this->assertSame('qwen3:8b', $this->invoke('normalizeOllamaModel', 'qwen3:8b'));
    }

    private function invoke(string $method, mixed ...$arguments): mixed
    {
        $reflection = new ReflectionMethod(RssArticleRewriteService::class, $method);

        return $reflection->invoke(new RssArticleRewriteService(), ...$arguments);
    }
}
