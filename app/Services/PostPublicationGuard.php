<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BannedPostWord;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostPublicationGuard
{
    /**
     * Returns the first active banned word/phrase found in a post.
     */
    public function findBannedWord(Post $post): ?string
    {
        $text = $this->normalizeText(implode("\n", array_filter([
            $post->title,
            $post->excerpt,
            $post->content,
            $post->meta_title,
            $post->meta_description,
            $post->meta_keywords,
            is_array($post->content_json) ? json_encode($post->content_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $post->content_json,
        ], static fn ($value): bool => filled($value))));

        if ($text === '') {
            return null;
        }

        foreach (BannedPostWord::query()->where('is_active', true)->pluck('word') as $word) {
            $normalizedWord = $this->normalizeText((string) $word);
            if ($normalizedWord === '') {
                continue;
            }

            $pattern = '/(?<![\p{L}\p{N}_])' . preg_quote($normalizedWord, '/') . '(?![\p{L}\p{N}_])/u';
            if (preg_match($pattern, $text) === 1) {
                return (string) $word;
            }
        }

        return null;
    }

    public function ensureAllowed(Post $post): void
    {
        $matched = $this->findBannedWord($post);
        if ($matched === null) {
            return;
        }

        throw ValidationException::withMessages([
            'title' => 'Bu gönderi yasaklı kelime/ifade içerdiği için yayınlanamaz ve kaydedilmez.',
        ]);
    }

    private function normalizeText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = str_replace(["\u{00A0}", "\u{200B}", "\u{200C}", "\u{200D}"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return Str::lower(trim($value));
    }
}
