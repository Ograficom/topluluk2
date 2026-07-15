<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureImageAltText;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class EnsureImageAltTextTest extends TestCase
{
    public function test_it_preserves_descriptive_alt_and_fills_missing_or_empty_alt_text(): void
    {
        $html = '<html><head><title>NATO Zirvesi - Ografi</title></head><body>'
            .'<img src="/uploads/nato-zirvesi.jpg">'
            .'<img src="/logo.png" alt="">'
            .'<img src="/author.jpg" alt="Yazar portresi">'
            .'</body></html>';

        $response = (new EnsureImageAltText())->handle(Request::create('/'), function () use ($html) {
            return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        });

        $content = (string) $response->getContent();

        $this->assertStringContainsString('alt="nato zirvesi görseli"', $content);
        $this->assertStringContainsString('alt="Ografi logosu"', $content);
        $this->assertStringContainsString('alt="Yazar portresi"', $content);
    }

    public function test_it_does_not_modify_non_html_responses(): void
    {
        $response = (new EnsureImageAltText())->handle(Request::create('/api'), function () {
            return new Response('{"image":"<img src=\"x.jpg\">"}', 200, ['Content-Type' => 'application/json']);
        });

        $this->assertStringNotContainsString('alt=', (string) $response->getContent());
    }
}
