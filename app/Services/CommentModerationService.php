<?php

namespace App\Services;

use App\Models\CommentBlockedWord;
use Illuminate\Support\Facades\Cache;

class CommentModerationService
{
    private const CACHE_KEY = 'comment_blocked_words.active';

    public function censor(string $content): string
    {
        $censored = $content;

        foreach ($this->blockedWords() as $word) {
            if ($word === '') {
                continue;
            }

            $censored = preg_replace_callback(
                '/' . preg_quote($word, '/') . '/iu',
                fn (array $matches) => $this->mask($matches[0]),
                $censored
            ) ?? $censored;
        }

        return $censored;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function blockedWords(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return CommentBlockedWord::query()
                ->where('is_active', true)
                ->pluck('word')
                ->map(fn ($word) => $this->normalize((string) $word))
                ->filter()
                ->unique()
                ->values()
                ->all();
        });
    }

    private function normalize(string $value): string
    {
        $value = strip_tags($value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function mask(string $value): string
    {
        return preg_replace_callback(
            '/[^\s]/u',
            fn () => '*',
            $value
        ) ?? str_repeat('*', max(3, mb_strlen($value, 'UTF-8')));
    }
}
