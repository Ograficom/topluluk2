<?php

namespace App\Models;

use App\Collections\CommentMentionDatabaseCollection;
use Illuminate\Database\Eloquent\Model;

class CommentMention extends Model
{
    protected $guarded = [];

    public function newCollection(array $models = [])
    {
        return new CommentMentionDatabaseCollection($models);
    }
}
