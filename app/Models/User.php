<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'username', 'full_name',
        'signature', 'bio', 'avatar_path', 'website', 'instagram', 'telegram',
        'threads', 'youtube', 'timezone', 'show_adult_content', 'notify_replies',
        'notify_posts', 'notify_announcements', 'is_verified', 'verification_status',
        'verification_reason', 'verification_requested_at', 'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_verified' => 'boolean',
            'show_adult_content' => 'boolean',
            'notify_replies' => 'boolean',
            'notify_posts' => 'boolean',
            'notify_announcements' => 'boolean',
            'verification_requested_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $user->is_verified = $user->verification_status === 'approved';
        });
    }

    public function socialAccounts(): HasMany { return $this->hasMany(SocialAccount::class); }
    public function posts(): HasMany { return $this->hasMany(Post::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }
    public function following(): BelongsToMany { return $this->belongsToMany(self::class, 'user_follows', 'follower_id', 'following_id')->withTimestamps(); }
    public function channels(): BelongsToMany { return $this->belongsToMany(Channel::class, 'channel_subscriptions')->withTimestamps(); }
    public function followers(): BelongsToMany { return $this->belongsToMany(self::class, 'user_follows', 'following_id', 'follower_id')->withTimestamps(); }
    public function bookmarks(): BelongsToMany { return $this->belongsToMany(Post::class, 'post_bookmarks')->withTimestamps(); }
    public function conversations(): BelongsToMany { return $this->belongsToMany(Conversation::class, 'conversation_user')->withPivot('last_read_at')->withTimestamps(); }
    public function sentMessages(): HasMany { return $this->hasMany(Message::class); }
    public function reactions(): HasMany { return $this->hasMany(PostReaction::class); }

    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'user_blocks', 'user_id', 'blocked_user_id')->withTimestamps();
    }

    public function blockedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'user_blocks', 'blocked_user_id', 'user_id')->withTimestamps();
    }

    public function hasVerifiedBadge(): bool
    {
        return $this->is_verified && $this->verification_status === 'approved';
    }

    public function displayName(): string
    {
        $name = $this->full_name ?: $this->name ?: $this->username;
        if (is_string($name) && filter_var($name, FILTER_VALIDATE_EMAIL)) return (string) str($name)->before('@');
        return filled($name) ? (string) $name : 'OgraFi kullanıcısı';
    }

    public function publicUsername(): ?string
    {
        if (blank($this->username)) return null;
        if (filter_var($this->username, FILTER_VALIDATE_EMAIL)) return (string) str($this->username)->before('@');
        return $this->username;
    }

    public function avatarUrl(): string
    {
        return $this->avatar_path ? asset('storage/'.$this->avatar_path) : asset('assets/img/noavatar.png');
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at !== null && $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }
}
