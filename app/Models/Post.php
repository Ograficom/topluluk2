<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Notifications\CategoryPostPublishedNotification;
use App\Support\PostSeoText;
use App\Support\PrivacyVisibility;
use App\Services\IndexNowService;
use App\Services\SitemapManager;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Martin6363\FilamentSmartSeo\Models\SeoMetadata;
use Rankbeam\Seo\Traits\HasSEO;

class Post extends Model
{
    use HasFactory;
    use HasSEO;

    public const MAX_DRAFTS_PER_USER = 5;

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'noindex',
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
        'noindex' => 'boolean',
        'views_count' => 'integer',
        'content_json' => 'array',
    ];

    public function getTitleAttribute($value): string
    {
        return $this->normalizeTitleText($value);
    }

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $this->normalizeTitleText($value);
    }

    private function normalizeTitleText($value): string
    {
        $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Legacy SmartSEO relation kept for backwards compatibility while
     * Rankbeam owns the active SEO resolver and editor.
     */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoble');
    }

    public function seoOrNew(): SeoMetadata
    {
        return $this->seo()->firstOrNew([]);
    }

    public function getSEOTitle(): ?string
    {
        $title = trim((string) ($this->meta_title ?: $this->title));

        return $title !== '' ? $title : null;
    }

    public function getSEODescription(): ?string
    {
        $description = trim((string) $this->meta_description);
        if ($description === '') {
            $description = PostSeoText::description($this->excerpt, $this->content, $this->title);
        }

        return $description !== '' ? $description : null;
    }

    public function getSEOImage(): ?string
    {
        return $this->ogImageUrl() ?: $this->featured_image_url;
    }

    public function getSEORobots(): ?string
    {
        if ($this->noindex) {
            return 'noindex, follow';
        }

        return $this->isPublishedNow() ? null : 'noindex, nofollow';
    }

    public function getUrlForSEO(): string
    {
        try {
            return route('blog.post', ['post' => $this]);
        } catch (\Throwable $e) {
            return url('/blog/' . ltrim((string) $this->slug, '/'));
        }
    }

    public function getSEOContentFields(): array
    {
        return [
            'title',
            'meta_title',
            'meta_description',
            'excerpt',
            'content',
            'featured_image',
            'og_image',
            'noindex',
            'published_at',
            'is_published',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Post $post): void {
            $post->seo()->delete();
        });

        static::creating(function (Post $post): void {
            if ($post->is_published || ! $post->author_id) {
                return;
            }

            $draftCount = static::query()
                ->where('author_id', $post->author_id)
                ->where('is_published', false)
                ->count();

            if ($draftCount >= self::MAX_DRAFTS_PER_USER) {
                throw ValidationException::withMessages([
                    'is_published' => 'En fazla ' . self::MAX_DRAFTS_PER_USER . ' taslak saklayabilirsiniz. Yeni taslak oluşturmak için mevcut taslaklardan birini yayınlayın veya silin.',
                ]);
            }
        });

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

            if ($post->isPublishedNow()) {
                event(\App\Events\PostPublished::fromPost($post));
            }
        });

        static::updated(function (Post $post) {
            if (!$post->wasPublishedBeforeUpdate() && $post->isPublishedNow()) {
                $post->notifyCategoryFollowers();
                event(\App\Events\PostPublished::fromPost($post));
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

    public function rssItem(): HasOne
    {
        return $this->hasOne(RssItem::class);
    }

    public function scopeAiWritten(Builder $query): Builder
    {
        return $query->whereHas('rssItem', fn ($q) => $q->whereNotNull('ai_rewritten_at'));
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
        $query->where('is_published', true)
            ->where(function (Builder $query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });

        return PrivacyVisibility::apply(
            $query,
            $query->qualifyColumn('author_id'),
            'posts_visibility',
            auth()->user(),
        );
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

    public function ogImageUrl(): ?string
    {
        $path = trim((string) ($this->og_image ?: $this->seo?->og_image));
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
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
