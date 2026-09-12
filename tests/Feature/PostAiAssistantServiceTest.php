<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Services\AI\OpenAiService;
use App\Services\AI\PostAiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PostAiAssistantServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_rewrite_really_updates_and_saves_the_post_without_changing_slug(): void
    {
        $post = Post::query()->create([
            'title' => 'Eski başlık',
            'slug' => 'degismeyen-slug',
            'excerpt' => 'Eski kısa özet.',
            'content' => '<p>Bu eski içerik, yapay zekâ düzenleme testinde yeterince uzun bir gövde metni olarak kullanılmaktadır.</p>',
            'content_json' => [
                'time' => 1,
                'blocks' => [
                    ['type' => 'paragraph', 'data' => ['text' => 'Eski içerik']],
                ],
            ],
            'meta_title' => 'Eski meta başlık',
            'meta_description' => 'Eski meta açıklaması.',
            'meta_keywords' => 'eski,anahtar',
            'is_published' => false,
        ]);

        $openAi = Mockery::mock(OpenAiService::class);
        $openAi->shouldReceive('structured')
            ->once()
            ->andReturn([
                'title' => 'Yeni ve düzenlenmiş başlık',
                'excerpt' => 'Yeni kısa özet.',
                'content_html' => '<p>Bu metin gerçekten yeniden düzenlendi ve veritabanına kaydedildiğini doğrulamak için yeterince uzun tutuldu.</p><h2>Detay</h2><p>İkinci paragraf da EditorJS senkronizasyonunun oluştuğunu doğrular.</p>',
                'meta_title' => 'Yeni meta başlık',
                'meta_description' => 'Yeni meta açıklaması.',
                'meta_keywords' => 'yeni,duzenleme,test',
                'change_summary' => 'Başlık, özet, gövde ve SEO alanları düzenlendi.',
            ]);

        $result = (new PostAiAssistantService($openAi))->editAndSave(
            post: $post,
            operation: 'rewrite',
            model: 'gpt-5.6-terra',
        );

        $fresh = $post->fresh();

        $this->assertSame('Yeni ve düzenlenmiş başlık', $fresh->title);
        $this->assertSame('degismeyen-slug', $fresh->slug);
        $this->assertSame('Yeni kısa özet.', $fresh->excerpt);
        $this->assertSame('Yeni meta başlık', $fresh->meta_title);
        $this->assertSame('Yeni meta açıklaması.', $fresh->meta_description);
        $this->assertSame('yeni,duzenleme,test', $fresh->meta_keywords);
        $this->assertStringContainsString('gerçekten yeniden düzenlendi', $fresh->content);
        $this->assertNotNull($fresh->edited_at);
        $this->assertStringContainsString('OpenAI:', (string) $fresh->edited_reason);
        $this->assertIsArray($fresh->content_json);
        $this->assertNotEmpty($fresh->content_json['blocks'] ?? []);
        $this->assertSame('paragraph', $fresh->content_json['blocks'][0]['type'] ?? null);
        $this->assertSame('Başlık, özet, gövde ve SEO alanları düzenlendi.', $result['change_summary']);
    }

    public function test_content_rewrite_is_blocked_when_post_contains_media_and_nothing_is_saved(): void
    {
        $originalContent = '<p>Medya içeren gönderinin mevcut metni korunmalıdır.</p><img src="/storage/test.jpg" alt="Test">';

        $post = Post::query()->create([
            'title' => 'Medya içeren gönderi',
            'slug' => 'medya-iceren-gonderi',
            'excerpt' => 'Mevcut özet.',
            'content' => $originalContent,
            'content_json' => [
                'time' => 1,
                'blocks' => [
                    ['type' => 'paragraph', 'data' => ['text' => 'Metin']],
                    ['type' => 'image', 'data' => ['file' => ['url' => '/storage/test.jpg']]],
                ],
            ],
            'is_published' => false,
        ]);

        $openAi = Mockery::mock(OpenAiService::class);
        $openAi->shouldNotReceive('structured');

        try {
            (new PostAiAssistantService($openAi))->editAndSave(
                post: $post,
                operation: 'rewrite',
                model: 'gpt-5.6-terra',
            );

            $this->fail('Medya içeren gönderide rewrite işlemi engellenmeliydi.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Medya bloklarini bozmamak', $exception->getMessage());
        }

        $fresh = $post->fresh();

        $this->assertSame('Medya içeren gönderi', $fresh->title);
        $this->assertSame($originalContent, $fresh->content);
        $this->assertNull($fresh->edited_at);
        $this->assertNull($fresh->edited_reason);
    }
}
