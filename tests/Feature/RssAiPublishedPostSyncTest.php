<?php

namespace Tests\Feature;

use App\Models\RssItem;
use App\Services\Rss\RssArticleRewriteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RssAiPublishedPostSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_rewrite_replaces_linked_raw_rss_post_even_when_regular_updates_are_disabled(): void
    {
        $now = now();

        $feedId = DB::table('rss_feeds')->insertGetId([
            'name' => 'Test Feed',
            'url' => 'https://example.test/feed.xml',
            'is_enabled' => true,
            'import_as_posts' => true,
            'auto_publish' => true,
            'update_existing_posts' => false,
            'ai_rewrite_enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $postId = DB::table('posts')->insertGetId([
            'title' => 'Kaynak baslik',
            'slug' => 'kaynak-baslik',
            'excerpt' => 'Kaynak ozet',
            'content' => '<p>RSS kaynagindan aynen alinmis eski metin.</p>',
            'is_published' => true,
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $hash = hash('sha256', 'source-v1');
        $itemId = DB::table('rss_items')->insertGetId([
            'rss_feed_id' => $feedId,
            'post_id' => $postId,
            'guid' => 'item-1',
            'title' => 'Kaynak baslik',
            'content' => '<p>RSS kaynagindan aynen alinmis eski metin.</p>',
            'hash' => $hash,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $item = RssItem::findOrFail($itemId);
        $item->forceFill([
            'ai_source_hash' => RssArticleRewriteService::expectedSourceHash($hash),
            'ai_title' => 'Yapay zekanin yeniden kurdugu baslik',
            'ai_summary' => 'Kaynak olgular korunarak uretilen yeni ozet.',
            'ai_content' => '<p>Kaynak bilgiler korunarak tamamen yeni cumlelerle yazilan ozgun haber metni.</p>',
            'ai_rewritten_at' => now(),
        ])->save();

        $post = DB::table('posts')->where('id', $postId)->first();

        $this->assertSame('Yapay zekanin yeniden kurdugu baslik', $post->title);
        $this->assertSame('Kaynak olgular korunarak uretilen yeni ozet.', $post->excerpt);
        $this->assertSame(
            '<p>Kaynak bilgiler korunarak tamamen yeni cumlelerle yazilan ozgun haber metni.</p>',
            $post->content,
        );
    }

    public function test_stale_ai_rewrite_does_not_replace_post_after_source_changes(): void
    {
        $now = now();
        $feedId = DB::table('rss_feeds')->insertGetId([
            'url' => 'https://example.test/feed.xml',
            'is_enabled' => true,
            'import_as_posts' => true,
            'update_existing_posts' => false,
            'ai_rewrite_enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $postId = DB::table('posts')->insertGetId([
            'title' => 'Mevcut post',
            'slug' => 'mevcut-post',
            'content' => '<p>Mevcut icerik.</p>',
            'is_published' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $itemId = DB::table('rss_items')->insertGetId([
            'rss_feed_id' => $feedId,
            'post_id' => $postId,
            'guid' => 'item-2',
            'hash' => hash('sha256', 'new-source'),
            'ai_source_hash' => RssArticleRewriteService::expectedSourceHash(hash('sha256', 'old-source')),
            'ai_title' => 'Eski AI basligi',
            'ai_content' => '<p>Eski AI icerigi.</p>',
            'ai_rewritten_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        RssItem::findOrFail($itemId)->touch();

        $this->assertSame('Mevcut post', DB::table('posts')->where('id', $postId)->value('title'));
        $this->assertSame('<p>Mevcut icerik.</p>', DB::table('posts')->where('id', $postId)->value('content'));
    }
}
