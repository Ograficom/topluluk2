<?php

namespace App\Console\Commands;

use App\Services\AiLiveModerationService;
use Illuminate\Console\Command;

class RunAiLiveModeration extends Command
{
    protected $signature = 'moderation:ai-scan {--limit=}';

    protected $description = 'Queue review-only AI moderation reports for recent posts, comments and profiles';

    public function handle(AiLiveModerationService $moderation): int
    {
        $limit = (int) ($this->option('limit') ?: config('ai-moderation.scan_limit', 8));
        $result = $moderation->scan($limit);
        $this->info("AI moderation: scanned={$result['scanned']} flagged={$result['flagged']} errors={$result['errors']}");

        return $result['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
