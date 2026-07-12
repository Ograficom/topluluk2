<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mews\Purifier\Casts\CleanHtmlInput;

class Poll extends Model
{
    protected $guarded = [];

    protected $casts = [
        'poll_ends' => 'datetime',
        'question' => CleanHtmlInput::class,
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class, 'story_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(PollChoice::class);
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }

    public function displayHumanTimeLeft()
    {
        $now = Carbon::now();
        if ($this->poll_ends >= $now) {
            if ($now->diffInMinutes($this->poll_ends) > 60) {
                return __('Time left').' '.$now->diffInHours($this->poll_ends).' '.trans_choice('hour', 2);
            } else {
                return __('Time left').' '.$now->diffInMinutes($this->poll_ends).' '.trans_choice('minute', 2);
            }
        } else {
            return __('Poll ended').' - '.Carbon::parse($this->poll_ends)->locale(str_replace('_', '-', app()->getLocale()))->isoFormat('MMMM Do YYYY');
        }
    }
}
