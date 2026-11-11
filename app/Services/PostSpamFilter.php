<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Str;

class PostSpamFilter
{
    public function shouldFlag(string $title, string $excerpt, string $body): bool
    {
        $settings = SiteSetting::current();

        if (! $settings || ! $settings->post_spam_enabled) {
            return false;
        }

        $content = $this->normalize($title.' '.$excerpt.' '.$body);
        if ($content === '') {
            return false;
        }

        foreach ($settings->post_spam_words ?? [] as $word) {
            if (! is_string($word) || trim($word) === '') {
                continue;
            }

            $needle = $this->normalize($word);
            if ($needle !== '' && preg_match('/(?:^| )'.preg_quote($needle, '/').'(?:$| )/u', $content) === 1) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        $value = Str::lower(strip_tags($value));
        $value = strtr($value, ['ı' => 'i', 'İ' => 'i', 'ş' => 's', 'ğ' => 'g', 'ü' => 'u', 'ö' => 'o', 'ç' => 'c']);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }
}
