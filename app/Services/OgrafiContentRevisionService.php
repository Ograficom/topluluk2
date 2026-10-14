<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostContentRevision;
use Illuminate\Support\Facades\DB;

class OgrafiContentRevisionService
{
    public function restore(Post $post, PostContentRevision $revision, ?string $reason = null): Post
    {
        abort_unless((int) $revision->post_id === (int) $post->getKey(), 404);

        return DB::transaction(function () use ($post, $revision, $reason): Post {
            // Preserve the current state before restoring an older snapshot.
            $post->title = $revision->title ?? $post->title;
            $post->excerpt = $revision->excerpt;
            $post->content = $revision->content;
            $post->content_json = $revision->content_json;
            $post->builder_content = $revision->builder_content;
            $post->meta_title = $revision->meta_title;
            $post->meta_description = $revision->meta_description;
            $post->edited_reason = $reason ?: 'İçerik sürümü geri yüklendi';
            $post->save();

            return $post->refresh();
        });
    }
}
