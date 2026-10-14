<?php

namespace App\Observers;

use App\Models\Post;
use App\Models\PostContentRevision;
use Illuminate\Support\Facades\Auth;

class PostContentRevisionObserver
{
    private const TRACKED_FIELDS = [
        'title',
        'excerpt',
        'content',
        'content_json',
        'builder_content',
        'meta_title',
        'meta_description',
    ];

    public function updating(Post $post): void
    {
        $changed = array_values(array_filter(
            self::TRACKED_FIELDS,
            fn (string $field): bool => $post->isDirty($field),
        ));

        if ($changed === []) {
            return;
        }

        $snapshot = [];
        foreach (self::TRACKED_FIELDS as $field) {
            $snapshot[$field] = $post->getRawOriginal($field);
        }

        PostContentRevision::query()->create([
            'post_id' => $post->getKey(),
            'user_id' => Auth::id(),
            'revision_type' => in_array('builder_content', $changed, true) ? 'builder_content' : 'content',
            'title' => $snapshot['title'],
            'excerpt' => $snapshot['excerpt'],
            'content' => $snapshot['content'],
            'content_json' => self::decodeJson($snapshot['content_json']),
            'builder_content' => self::decodeJson($snapshot['builder_content']),
            'meta_title' => $snapshot['meta_title'],
            'meta_description' => $snapshot['meta_description'],
            'snapshot' => $snapshot,
            'revision_reason' => filled($post->edited_reason) ? \Illuminate\Support\Str::limit(trim((string) $post->edited_reason), 500, '') : null,
            'created_at' => now(),
        ]);
    }

    private static function decodeJson(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}
