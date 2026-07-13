<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PostLinkPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_feed_renders_link_preview_for_plain_urls(): void
    {
        Http::fake([
            'https://example.com/article' => Http::response(<<<'HTML'
                <html>
                    <head>
                        <title>Example Story</title>
                        <meta property="og:description" content="Example description for preview.">
                        <meta property="og:image" content="https://cdn.example.com/preview.jpg">
                        <meta property="og:site_name" content="Example News">
                    </head>
                    <body></body>
                </html>
            HTML, 200),
        ]);

        Post::create([
            'title' => 'Preview test post',
            'slug' => 'preview-test-post',
            'content' => '<p>Bu linke bak: https://example.com/article</p>',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.index'));

        $response
            ->assertOk()
            ->assertSee('Example Story')
            ->assertSee('example.com')
            ->assertSee('Example description for preview.')
            ->assertSee('https://cdn.example.com/preview.jpg', false)
            ->assertSee('.alma-link-preview__eyebrow', false)
            ->assertSee('color: #475569;', false)
            ->assertSee('html.dark .alma-link-preview__eyebrow', false)
            ->assertSee('color: #cbd5e1;', false);
    }
}
