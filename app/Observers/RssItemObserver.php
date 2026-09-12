<?php

namespace App\Observers;

use App\Models\RssItem;
use App\Services\Rss\RssArticleRewriteService;
use App\Support\PostSeoText;

class RssItemObserver
{
    /**
     * Keep an already-linked RSS post synchronized with its approved AI rewrite.
     * update_existing_posts controls ordinary source refreshes; it must not stop
     * an enabled AI rewrite from replacing the raw RSS article in the published post.
     */
    public function saved(RssItem $item): void
    {
        if (
            ! $item->post_id
            || ! $item->ai_rewritten_at
            || blank($item->ai_title)
            || blank($item->ai_content)
            || blank($item->hash)
            || $item->ai_source_hash !== RssArticleRewriteService::expectedSourceHash((string) $item->hash)
        ) {
            return;
        }

        $feed = $item->feed()->first();
        if (! $feed || ! $feed->ai_rewrite_enabled || ! $feed->import_as_posts) {
            return;
        }

        $post = $item->post()->first();
        if (! $post) {
            return;
        }

        $title = trim((string) $item->ai_title);
        $excerpt = trim((string) ($item->ai_summary ?? ''));
        $content = trim((string) $item->ai_content);
        $dirty = false;

        if ((string) $post->title !== $title) {
            $post->title = $title;
            $post->meta_title = PostSeoText::title($title);
            $dirty = true;
        }

        if ((string) ($post->excerpt ?? '') !== $excerpt) {
            $post->excerpt = $excerpt !== '' ? $excerpt : null;
            $post->meta_description = PostSeoText::description($excerpt);
            $dirty = true;
        }

        if (trim((string) $post->content) !== $content) {
            $post->content = $content;
            $post->content_json = null;
            $dirty = true;
        }

        if ($dirty) {
            $post->saveQuietly();
        }
    }
}
