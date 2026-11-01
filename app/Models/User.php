<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'username',
        'full_name',
        'signature',
        'bio',
        'avatar_path',
        'website',
        'instagram',
        'telegram',
        'threads',
        'youtube',
        'timezone',
        'show_adult_content',
        'notify_replies',
        'notify_posts',
        'notify_announcements',
        'is_verified',
        'verification_status',
        'verification_reason',
        'verification_requested_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $user->is_verified = $user->verification_status === 'approved';
        });
    }

    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    public function blockedUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'user_blocks',
            'user_id',
            'blocked_user_id',
        )->withTimestamps();
    }

    public function blockedByUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'user_blocks',
            'blocked_user_id',
            'user_id',
        )->withTimestamps();
    }

    public function hasVerifiedBadge(): bool
    {
        return $this->is_verified && $this->verification_status === 'approved';
    }

    public function avatarUrl(): string
    {
        return $this->avatar_path
            ? asset('storage/' . $this->avatar_path)
            : asset('assets/img/noavatar.png');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }
}
