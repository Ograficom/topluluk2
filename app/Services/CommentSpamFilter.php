<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\SiteSetting;
use Illuminate\Support\Str;

class CommentSpamFilter
{
    public function shouldFlag(int $userId, string $body): bool
    {
        $settings = SiteSetting::current();

        if (! $settings || ! $settings->comment_spam_enabled) {
            return false;
        }

        $normalized = $this->normalize($body);
        if ($normalized === '') {
            return false;
        }

        $words = collect($settings->comment_spam_words ?? [])
            ->filter(fn ($word): bool => is_string($word) && trim($word) !== '')
            ->map(fn (string $word): string => $this->normalize($word))
            ->filter()
            ->unique()
            ->values();

        foreach ($words as $word) {
            if (str_contains($normalized, $word)) {
                return true;
            }
        }

        $maxLinks = max(0, (int) ($settings->comment_spam_max_links ?? 2));
        preg_match_all('~(?:https?://|www\.)[^\s<]+~iu', $body, $matches);
        if (count($matches[0] ?? []) > $maxLinks) {
            return true;
        }

        $rateLimit = max(1, (int) ($settings->comment_spam_rate_limit ?? 4));
        if (Comment::query()->where('user_id', $userId)->where('created_at', '>=', now()->subMinute())->count() >= $rateLimit) {
            return true;
        }

        return Comment::query()
            ->where('user_id', $userId)
            ->where('body', trim($body))
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(strip_tags($value));
        $value = strtr($value, ['ı' => 'i', 'İ' => 'i', 'ş' => 's', 'ğ' => 'g', 'ü' => 'u', 'ö' => 'o', 'ç' => 'c']);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }
}
