<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $table = 'conversations';

    protected $guarded = [];

    protected $withCount = ['messages'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->messagesWithoutDeleted()->orWhere(function ($query) {
            $query->whereNull('deleted_at');
        });
    }

    public function messagesWithoutDeleted(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadCountForUser(int $userId): int
    {
        $lastRead = $this->users()
            ->where('user_id', $userId)
            ->first()?->pivot?->last_read_at;

        if (! $lastRead) {
            return $this->messages()
                ->where('user_id', '!=', $userId)
                ->count();
        }

        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->where('created_at', '>', $lastRead)
            ->count();
    }

    public function markAsReadForUser(int $userId): void
    {
        $this->users()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        $this->messages()
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
