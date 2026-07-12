<?php

namespace App\Events\Report;

use Illuminate\Queue\SerializesModels;

class CommentReported
{
    use SerializesModels;

    public function __construct(public $userId, public $commentId, public string $reason, public ?string $message)
    {
    }
}
