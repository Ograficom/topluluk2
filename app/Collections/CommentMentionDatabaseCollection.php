<?php

namespace App\Collections;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CommentMentionDatabaseCollection extends Collection
{
    public function users()
    {
        return User::whereIn('username', $this->pluck('body_plain'))->get();
    }
}
