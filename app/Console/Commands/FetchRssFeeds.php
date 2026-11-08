<?php

namespace App\Console\Commands;

use App\Services\ExternalRssReader;
use Illuminate\Console\Command;

class FetchRssFeeds extends Command
{
    protected $signature = 'rss:fetch';
    protected $description = 'Etkin dış RSS kaynaklarını günceller.';

    public function handle(ExternalRssReader $reader): int
    {
        $result = $reader->fetchAll();
        $this->info("Kaynak: {$result['sources']} · Yeni/güncellenen öğe: {$result['items']} · Hata: {$result['errors']}");

        return $result['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
