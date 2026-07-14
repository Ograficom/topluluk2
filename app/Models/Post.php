<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Notifications\CategoryPostPublishedNotification;
use App\Support\PostSeoText;
use App\Services\IndexNowService;
use App\Services\SitemapManager;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'excerpt',
        'featured_image',
        'image_license_url',
        'image_acquire_url',
        'image_credit_text',
        'image_creator_name',
        'image_copyright_notice',
        'content',
        'content_json',
        'published_at',
        'edited_at',
        'edited_reason',
        'is_published',
        'is_pinned',
        'comments_disabled',
        'is_nsfw',
        'views_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'edited_at' => 'datetime',
        'is_published' => 'boolean',
        'is_pinned' => 'boolean',
        'comments_disabled' => 'boolean',
        'is_nsfw' => 'boolean',
        'views_count' => 'integer',
        'content_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (blank($post->meta_title)) {
                $post->meta_title = PostSeoText::title($post->title);
            }
            if (blank($post->meta_description)) {
                $post->meta_description = PostSeoText::description($post->excerpt, $post->content, $post->title);
            }
        });

        static::created(function (Post $post) {
            $post->notifyCategoryFollowersIfPublished();
        });

        static::updated(function (Post $post) {
            if (!$post->wasPublishedBeforeUpdate() && $post->isPublishedNow()) {
                $post->notifyCategoryFollowers();
            }
        });

        static::saved(function (Post $post) {
            if (! $post->isPublishedNow()) {
                return;
            }

            try {
                app(SitemapManager::class)->regenerate();
                $url = route('blog.post', ['post' => $post]);
                app(IndexNowService::class)->queue($url);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function latestComment(): HasOne
    {
        return $this->hasOne(Comment::class)->ofMany(
            ['created_at' => 'max', 'id' => 'max'],
            function (Builder $query) {
                $query->approved()->whereNull('parent_id');
            }
        );
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PostReport::class);
    }

    public function bookmarkers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks')->withTimestamps();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(function (Builder $query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        if (Str::startsWith($this->featured_image, ['http://', 'https://', '//'])) {
            $parsedHost = parse_url($this->featured_image, PHP_URL_HOST);
            $parsedPath = parse_url($this->featured_image, PHP_URL_PATH);

            // Normalize old localhost/127 URLs to current app host.
            if (is_string($parsedPath) && Str::startsWith($parsedPath, '/storage/') && in_array($parsedHost, ['localhost', '127.0.0.1'], true)) {
                return url($parsedPath);
            }

            return $this->featured_image;
        }

        if (Str::startsWith($this->featured_image, '/storage/')) {
            return url($this->featured_image);
        }

        if (Str::startsWith($this->featured_image, 'storage/')) {
            return url('/storage/' . Str::after($this->featured_image, 'storage/'));
        }

        return Storage::disk('public')->url($this->featured_image);
    }

    public function isPublishedNow(): bool
    {
        return $this->is_published && ($this->published_at === null || $this->published_at->isPast());
    }

    private function wasPublishedBeforeUpdate(): bool
    {
        $wasPublished = (bool) $this->getOriginal('is_published');
        $publishedAt = $this->getOriginal('published_at');
        if (!$wasPublished) {
            return false;
        }

        if ($publishedAt === null) {
            return true;
        }

        $publishedAt = $publishedAt instanceof Carbon ? $publishedAt : Carbon::parse($publishedAt);

        return now()->greaterThanOrEqualTo($publishedAt);
    }

    public function notifyCategoryFollowersIfPublished(): void
    {
        if (!$this->isPublishedNow()) {
            return;
        }

        $this->notifyCategoryFollowers();
    }

    public function notifyCategoryFollowers(): void
    {
        if (!$this->category_id) {
            return;
        }

        $category = $this->category ?? $this->category()->first();
        if (!$category) {
            return;
        }

        $followers = $category->followers()
            ->when($this->author_id, fn ($q) => $q->whereKeyNot($this->author_id))
            ->get();

        if ($followers->isEmpty()) {
            return;
        }

        Notification::send($followers, new CategoryPostPublishedNotification($this));
    }
}
