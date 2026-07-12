<?php

namespace App\Events\Story;

use App\Models\Story;
use Illuminate\Queue\SerializesModels;

class CreatedPublishedStory
{
    use SerializesModels;

    public $story;

    public function __construct(Story $story)
    {
        $this->story = $story;
    }
}
