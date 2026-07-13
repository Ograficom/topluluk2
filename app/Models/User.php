<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\BadgeAwardSyncService;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_WRITER = 'writer';

    public const ROLE_BANNED = 'banned';

    public const LEGACY_ROLE_USER = 'user';

    public const LEGACY_ROLE_BLOCKED = 'blocked';

    public const LEGACY_ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'block_messages',
        'block_posts',
        'block_categories',
        'block_tags',
        'block_comments',
        'block_reactions',
        'profile_photo_path',
        'cover_photo_path',
        'bio',
        'location',
        'company',
        'education',
        'social_provider',
        'social_provider_id',
        'social_x',
        'social_instagram',
        'social_whatsapp',
        'social_tiktok',
        'social_facebook',
        'social_youtube',
        'website_url',
        'joined_at',
        'is_verified',
        'verification_badge',
        'verification_badge_svg',
        'profile_type',
        'badge_points',
        'profile_completed_rewarded_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
            'joined_at' => 'datetime',
            'is_verified' => 'boolean',
            'block_messages' => 'boolean',
            'block_posts' => 'boolean',
            'block_categories' => 'boolean',
            'block_tags' => 'boolean',
            'block_comments' => 'boolean',
            'block_reactions' => 'boolean',
            'badge_points' => 'integer',
            'profile_completed_rewarded_at' => 'datetime',
        ];
    }

    public function earnedBadges()
    {
        if (!Schema::hasTable('badge_user')) {
            return Badge::query()
                ->active()
                ->eligibleForUser($this)
                ->where('min_points', '<=', (int) $this->badge_points)
                ->orderByDesc('min_points')
                ->orderBy('id');
        }

        return $this->awardedBadges()
            ->where('badges.is_active', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('badges.eligible_profile_type')
                    ->orWhere('badges.eligible_profile_type', '')
                    ->orWhere('badges.eligible_profile_type', strtolower(trim((string) ($this->profile_type ?? 'person'))));
            })
            ->when(!(bool) $this->is_verified, fn ($query) => $query->where('badges.requires_verified', false))
            ->where('badges.min_points', '<=', (int) $this->badge_points)
            ->orderByDesc('badges.min_points')
            ->orderBy('badges.id');
    }

    public function earnedBadgesCollection(): Collection
    {
        if (Schema::hasTable('badge_user')) {
            app(BadgeAwardSyncService::class)->syncForUser($this);
        }

        return $this->earnedBadges()->get();
    }

    public function nextBadge(): ?Badge
    {
        return Badge::query()
            ->active()
            ->eligibleForUser($this)
            ->where('min_points', '>', (int) $this->badge_points)
            ->orderBy('min_points')
            ->first();
    }

    public static function roleOptions(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_EDITOR => 'Editor',
            self::ROLE_WRITER => 'Yazar',
            self::ROLE_BANNED => 'Banned',
        ];
    }

    public static function roleDescriptions(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin paneline erisir, kullanicilari ve tum ayarlari yonetir.',
            self::ROLE_EDITOR => 'Icerik duzenler, kategori ve etiket yonetebilir ama admin paneline giremez.',
            self::ROLE_WRITER => 'Yazi ve yorum uretir, kategori ve etiket acamaz, admin paneline giremez.',
            self::ROLE_BANNED => 'Tum icerik ve etkilesim yetkileri kapatilir, admin paneline giremez.',
        ];
    }

    public static function restrictionLabels(): array
    {
        return [
            'admin' => 'admin paneli',
            'messages' => 'mesaj',
            'posts' => 'gonderi',
            'categories' => 'kategori',
            'tags' => 'etiket',
            'comments' => 'yorum',
            'reactions' => 'reaksiyon',
        ];
    }

    public static function manualRestrictionFields(): array
    {
        return [
            'block_messages',
            'block_posts',
            'block_categories',
            'block_tags',
            'block_comments',
            'block_reactions',
        ];
    }

    public static function normalizeRoleValue(?string $role): string
    {
        $role = trim((string) $role);

        return match ($role) {
            self::ROLE_ADMIN,
            self::ROLE_EDITOR,
            self::ROLE_WRITER,
            self::ROLE_BANNED => $role,
            self::LEGACY_ROLE_SUPER_ADMIN => self::ROLE_ADMIN,
            self::LEGACY_ROLE_BLOCKED => self::ROLE_BANNED,
            self::LEGACY_ROLE_USER, '' => self::ROLE_WRITER,
            default => self::ROLE_WRITER,
        };
    }

    public static function roleBaseRestrictions(?string $role): array
    {
        return match (self::normalizeRoleValue($role)) {
            self::ROLE_ADMIN => [],
            self::ROLE_EDITOR => ['admin'],
            self::ROLE_WRITER => ['admin', 'categories', 'tags'],
            self::ROLE_BANNED => ['admin', 'messages', 'posts', 'categories', 'tags', 'comments', 'reactions'],
            default => ['admin', 'categories', 'tags'],
        };
    }

    public static function roleBaseRestrictionLabels(?string $role): array
    {
        $labels = self::restrictionLabels();

        return collect(self::roleBaseRestrictions($role))
            ->map(fn (string $ability) => $labels[$ability] ?? $ability)
            ->values()
            ->all();
    }

    public function normalizedRole(): string
    {
        return self::normalizeRoleValue($this->role);
    }

    public function roleLabel(): string
    {
        return self::roleOptions()[$this->normalizedRole()] ?? 'Yazar';
    }

    public function isAdmin(): bool
    {
        return $this->normalizedRole() === self::ROLE_ADMIN;
    }

    public function isEditor(): bool
    {
        return $this->normalizedRole() === self::ROLE_EDITOR;
    }

    public function isWriter(): bool
    {
        return $this->normalizedRole() === self::ROLE_WRITER;
    }

    public function isBanned(): bool
    {
        return $this->normalizedRole() === self::ROLE_BANNED;
    }

    public function isLastAdmin(): bool
    {
        if (!$this->exists || !$this->isAdmin()) {
            return false;
        }

        return !static::query()
            ->where('role', self::ROLE_ADMIN)
            ->whereKeyNot($this->getKey())
            ->exists();
    }

    public function isBlockedRole(): bool
    {
        return $this->isBanned();
    }

    public function isBlockedFrom(string $capability): bool
    {
        $capability = strtolower(trim($capability));

        if (in_array($capability, self::roleBaseRestrictions($this->role), true)) {
            return true;
        }

        return match ($capability) {
            'messages' => (bool) $this->block_messages,
            'posts' => (bool) $this->block_posts,
            'categories' => (bool) $this->block_categories,
            'tags' => (bool) $this->block_tags,
            'comments' => (bool) $this->block_comments,
            'reactions' => (bool) $this->block_reactions,
            default => false,
        };
    }

    public function blockedAbilityLabels(): array
    {
        $labels = self::roleBaseRestrictionLabels($this->role);

        if ($this->block_messages) $labels[] = 'mesaj';
        if ($this->block_posts) $labels[] = 'gonderi';
        if ($this->block_categories) $labels[] = 'kategori';
        if ($this->block_tags) $labels[] = 'etiket';
        if ($this->block_comments) $labels[] = 'yorum';
        if ($this->block_reactions) $labels[] = 'reaksiyon';

        return array_values(array_unique($labels));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->isAdmin()
            && !$this->isBlockedFrom('admin');
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->role)) {
                $user->role = static::query()->exists() ? self::ROLE_WRITER : self::ROLE_ADMIN;
            }

            $user->role = self::normalizeRoleValue($user->role);

            foreach (self::manualRestrictionFields() as $field) {
                if (!array_key_exists($field, $user->attributes)) {
                    $user->{$field} = false;
                }
            }

            if (empty($user->username)) {
                $user->username = static::generateUniqueUsername($user->name ?? 'user');
            }
        });

        static::saving(function (User $user) {
            $user->role = self::normalizeRoleValue($user->role);

            foreach (self::manualRestrictionFields() as $field) {
                $user->{$field} = (bool) ($user->{$field} ?? false);
            }

            if ($user->isBanned()) {
                foreach (self::manualRestrictionFields() as $field) {
                    $user->{$field} = true;
                }
            }
        });
    }

    public static function generateUniqueUsername(string $name): string
    {
        $base = Str::slug($name) ?: 'user';
        $username = $base;
        $suffix = 1;

        while (static::where('username', $username)->exists()) {
            $username = $base . '-' . $suffix;
            $suffix++;
        }

        return $username;
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'bookmarks')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id');
    }

    public function followings(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id');
    }

    public function followedCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_user')->withTimestamps();
    }

    public function awardedBadges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_user')
            ->withPivot(['awarded_points', 'awarded_at'])
            ->withTimestamps();
    }

    public function cookieConsents(): HasMany
    {
        return $this->hasMany(CookieConsent::class);
    }

    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocker_id', 'blocked_id');
    }

    public function blockers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocked_id', 'blocker_id');
    }

    public function hasBlocked(User $user): bool
    {
        return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
    }

    public function isBlockedBy(User $user): bool
    {
        return $this->blockers()->where('blocker_id', $user->id)->exists();
    }

    public function getCoverPhotoUrlAttribute(): string
    {
        if ($this->cover_photo_path) {
            $path = $this->cover_photo_path;

            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }

            if (Str::startsWith($path, ['http://', 'https://', '//'])) {
                return $path;
            }

            if (Str::startsWith($path, '/storage/')) {
                return url($path);
            }

            if (Str::startsWith($path, 'storage/')) {
                return url('/storage/' . Str::after($path, 'storage/'));
            }
        }

        return 'https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=1200&q=80';
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        $path = $this->profile_photo_path;

        if ($path && Storage::disk($this->profilePhotoDisk())->exists($path)) {
            return Storage::disk($this->profilePhotoDisk())->url($path);
        }

        if ($path && Str::startsWith($path, ['http://', 'https://', '//'])) {
            $parsedHost = parse_url($path, PHP_URL_HOST);
            $parsedPath = parse_url($path, PHP_URL_PATH);

            if (is_string($parsedPath) && Str::startsWith($parsedPath, '/storage/') && in_array($parsedHost, ['localhost', '127.0.0.1'], true)) {
                return url($parsedPath);
            }

            return $path;
        }

        if ($path && Str::startsWith($path, '/storage/')) {
            return url($path);
        }

        if ($path && Str::startsWith($path, 'storage/')) {
            return url('/storage/' . Str::after($path, 'storage/'));
        }

        return $this->defaultProfilePhotoUrl();
    }
}
