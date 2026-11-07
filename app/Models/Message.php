<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'body', 'read_at', 'attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size', 'gif_url'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'attachment_size' => 'integer'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
