<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostReport extends Model
{
    public const TOPICS = [
        'Istenmeyen' => 'Istenmeyen',
        'Suistimal' => 'Suistimal',
        'Taciz' => 'Taciz',
        'Kanun ihlali' => 'Kanun ihlali',
        'Cinsel icerik' => 'Cinsel icerik',
        'Cinsellik' => 'Cinsellik',
        'Telif hakki sorunu' => 'Telif hakki sorunu',
        'Kimlik avi' => 'Kimlik avi',
        'Diger' => 'Diger',
    ];

    public const STATUSES = [
        'pending' => 'Beklemede',
        'in_review' => 'Incelemede',
        'resolved' => 'Cozuldu',
        'dismissed' => 'Kapandi',
    ];

    protected $fillable = [
        'reporter_id',
        'post_id',
        'topic',
        'description',
        'status',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
