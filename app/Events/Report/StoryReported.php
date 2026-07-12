<?php

namespace App\Events\Report;

use Illuminate\Queue\SerializesModels;

class StoryReported
{
    use SerializesModels;

    public function __construct(public $userId, public $storyId, public string $reason, public ?string $message)
    {
    }
}
