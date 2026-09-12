<?php

namespace App\Services\Rss;

use App\Models\RssItem;

class RssContentModerationService
{
    private const CLICKBAIT_PATTERNS = [
        '/\b(mi|mı|mu|mü)\s+oldu\s*\?/iu',
        '/\b(mi|mı|mu|mü)\s+öldü\s*\?/iu',
        '/\bvar\s*m[ıi]\s*\?/iu',
        '/\bgerçek\s*mi\s*\?/iu',
        '/\bdoğru\s*mu\s*\?/iu',
        '/\bnerede\s+oldu\s*\?/iu',
        '/\bne\s+zaman\s+oldu\s*\?/iu',
    ];

    public function looksLikeClickbaitQuestion(string $title): bool
    {
        $title = trim($title);

        if ($title === '' || ! str_ends_with($title, '?')) {
            return false;
        }

        foreach (self::CLICKBAIT_PATTERNS as $pattern) {
            if (preg_match($pattern, $title) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{title?: string, summary?: string, content?: string}  $rewritten
     * @return array{reject: bool, reject_reason: ?string, is_nsfw: bool, nsfw_reason: ?string}
     */
    public function moderate(RssItem $item, array $rewritten, ?string $imageUrl = null): array
    {
        $originalTitle = (string) ($item->title ?? '');
        $rewrittenTitle = (string) ($rewritten['title'] ?? '');
        $reject = $this->looksLikeClickbaitQuestion($originalTitle)
            || $this->looksLikeClickbaitQuestion($rewrittenTitle);

        return [
            'reject' => $reject,
            'reject_reason' => $reject ? 'Baslik tik tuzagi sorusu formatinda ("... mi oldu?" vb.)' : null,
            'is_nsfw' => false,
            'nsfw_reason' => null,
        ];
    }
}
