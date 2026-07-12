<?php

namespace App\Events\Comment;

use App\Models\Comment;
use Illuminate\Queue\SerializesModels;

class CreatedCommentReply
{
    use SerializesModels;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}
