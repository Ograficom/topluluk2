<?php

namespace App\Console\Commands;

use App\Support\Moderation\AiModeratorProfiles;
use Illuminate\Console\Command;

class BootstrapAiModerators extends Command
{
    protected $signature = 'moderation:ai-bootstrap';

    protected $description = 'Create the non-interactive system accounts used by AI moderation';

    public function handle(): int
    {
        $bots = AiModeratorProfiles::ensure();
        $this->info("{$bots->count()} AI moderation accounts are ready.");

        return self::SUCCESS;
    }
}
