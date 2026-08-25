<?php

namespace App\Console\Commands;

use App\Services\AiCommunityActivityService;
use Illuminate\Console\Command;

class RunAiCommunityActivity extends Command
{
    protected $signature = 'community:ai-engage {--limit=2 : Maximum number of posts to engage with}';

    protected $description = 'Create transparent AI community interactions for recent public posts';

    public function handle(AiCommunityActivityService $activity): int
    {
        $limit = max(1, min(5, (int) $this->option('limit')));
        $result = $activity->engage($limit);

        $this->info("AI community: comments={$result['comments']} reactions={$result['reactions']} skipped={$result['skipped']}");

        return self::SUCCESS;
    }
}
