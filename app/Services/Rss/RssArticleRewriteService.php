<?php

namespace App\Services\Rss;

use App\Models\RssItem;

class RssArticleRewriteService
{
    public const PROMPT_VERSION = 'disabled-2026-09';

    public static function expectedSourceHash(string $itemHash): string
    {
        return hash('sha256', self::PROMPT_VERSION.'|'.$itemHash);
    }

    /**
     * @return array{title: string, summary: string, content: string, tags: array<int, string>}
     */
    public function rewrite(RssItem $item, ?string $model = null): array
    {
        throw new \RuntimeException('RSS yapay zeka yeniden yazma özelliği devre dışı.');
    }
}
