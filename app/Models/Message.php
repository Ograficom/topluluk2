<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'body',
        'read_at',
        'attachment_path',
        'attachment_mime',
        'attachment_name',
        'attachment_size',
        'deleted_by_sender',
        'deleted_by_recipient',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'deleted_by_sender' => 'boolean',
        'deleted_by_recipient' => 'boolean',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
