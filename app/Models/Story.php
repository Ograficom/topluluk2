<?php

namespace App\Models;

use App\Enums\PostType;
use App\Models\Traits\Favoriteable;
use App\Models\Traits\Likeable;
use App\Services\EditorBlocksService;
use Carbon\Carbon;
use Cocur\Slugify\Slugify;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentTaggable\Taggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Casts\CleanHtmlInput;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Story extends Model implements HasMedia, Feedable
{
    use HasFactory;
    use Sluggable;
    use Taggable;
    use InteractsWithMedia;
    use Favoriteable;
    use Likeable;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * The attributes that should be casted.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
        'meta' => 'array',
        'title' => CleanHtmlInput::class,
        'subtitle' => CleanHtmlInput::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function () {
            Cache::forget('featuredStories');
            Cache::forget('popularTags');
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => ['id', 'title'],
            ],
        ];
    }

    public function customizeSlugEngine(Slugify $engine, $attribute)
    {
        $engine->activateRuleSet('korean');
        $engine->activateRuleSet('chinese');

        return $engine;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured-image')->singleFile();
        $this->addMediaCollection('story-audio')->singleFile();
    }

    public function toFeedItem(): FeedItem
    {
        $image = $this->getFirstMediaUrl();

        return FeedItem::create()
            ->id($this->id)
            ->title($this->title)
            ->summary($this->getSummary() ?? '')
            ->enclosure(isset($image) ? $image : settings()->group('general')->get('site_logo'))
            ->updated($this->updated_at)
            ->link(route('story.show', $this->slug))
            ->authorName($this->author->name ? $this->author->name : $this->author->username);
    }

    public function getFirstMediaUrl()
    {
        $collectionName = 'featured-image';

        if ($this->getMedia($collectionName)->isNotEmpty()) {
            return [
                'type' => 'image',
                'url' => $this->getFirstMedia($collectionName)->getUrl(),
            ];
        } else {
            $blocks = new EditorBlocksService($this->body);

            foreach ($blocks->getBlocks() as $block) {
                if ($block->type == 'image') {
                    return [
                        'type' => 'image',
                        'url' => $block->data->file->url,
                    ];
                }
                if ($block->type == 'video') {
                    return [
                        'type' => 'video',
                        'url' => $block->data->file->url,
                    ];
                }
                if ($block->type == 'embed') {
                    if ($block->data->service == 'youtube' || $block->data->service == 'vimeo') {
                        return [
                            'type' => 'embed',
                            'url' => $block->data->embed,
                        ];
                    }
                }
            }
        }
    }

    public function getFilamentMediaUrl()
    {
        if ($this->getMedia('featured-image')->isNotEmpty()) {
            return Storage::disk(getCurrentDisk())->url($this->getFirstMedia('featured-image')->getUrl());
        }

        $blocks = new EditorBlocksService($this->body);
        foreach ($blocks->getBlocks() as $block) {
            if ($block->type == 'image') {
                return url($block->data->file->url);
            }
        }

        return asset('images/nopreview.jpg');
    }

    public function getSummary()
    {
        return Cache::remember("story:{$this->id}:summary", now()->addMinutes(10), function () {
            $summary = html_entity_decode($this->body);

            return getFirstParagraph(strip_tags($summary));
        });
    }

    public function getFirstParagraph()
    {
        $blocks = new EditorBlocksService($this->body);
        foreach ($blocks->getBlocks() as $index => $block) {
            if ($index === 0 && $block->type == 'paragraph') {
                return html_entity_decode(strip_tags($block->data->text), ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
            }
        }
    }

    // RELATIONS

    public function community()
    {
        return $this->belongsTo(Community::class, 'community_id');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id');
    }

    public function allComments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function poll()
    {
        return $this->hasOne(Poll::class, 'story_id');
    }

    public function originalPost(): HasOne
    {
        return $this->hasOne(self::class, 'id', 'original_story_id')->with('user');
    }

    public function stories(): HasMany
    {
        return $this->hasMany(self::class, 'id');
    }

    public function reposts(): HasMany
    {
        return $this->hasMany(self::class, 'original_story_id');
    }

    public function isRepostedBy(Model $user): bool
    {
        // if ($this->relationLoaded('reposts')) {
        //     return $this->reposts->contains($user);
        // }

        return $this->reposts()->where('user_id', $user->getKey())->where('type', PostType::REPOST)->exists();
    }

    public function repostedStory()
    {
        return $this->hasOne(self::class, 'original_story_id', 'id');
    }

    public function isRepost(): bool
    {
        return $this->type === PostType::REPOST->value && $this->original_story_id !== null;
    }

    public function isNotPublished(): bool
    {
        return $this->published_at === null;
    }

    public function isPublished(): bool
    {
        return ! $this->isNotPublished();
    }

    public function isApproved(): bool
    {
        return $this->isPublished() && $this->approved_at !== null;
    }

    public function popularComment()
    {
        return $this->hasOne(Comment::class, 'commentable_id', 'id')
            ->select('id', 'commentable_id', 'user_id', 'comment', 'created_at')
            ->orderByRaw('(SELECT SUM(likes) FROM likes WHERE likes.likeable_id = comments.id) DESC')
            ->with('user:id,name,username,avatar');
    }

    public function participants()
    {
        return $this->hasManyThrough(User::class, Comment::class, 'commentable_id', 'id', 'id', 'user_id')
            ->select('users.id', 'users.name', 'users.username', 'users.avatar', 'comments.commentable_id', 'comments.user_id')
            ->distinct()
            ->orderBy('users.username', 'DESC')
            ->limit(3);
    }

    public function isCommunities(): bool
    {
        return $this->community_id !== null;
    }

    public function isPinned(): bool
    {
        return (bool) $this->is_pinned;
    }

    public function isCommentsDisabled(): bool
    {
        return (bool) $this->is_comments_disabled;
    }

    public function userPinnedStories()
    {
        return self::where('user_id', '=', getCurrentUser()->id)->published()->pinned()->get();
    }

    public function getShortContentAttribute()
    {
        return substr($this->body, 0, random_int(150, 300)).'...';
    }

    public function readTime()
    {
        $minutes = round(str_word_count(strip_tags($this->body)) / 100);

        return ($minutes > 1) ? $minutes.' '.__('minutes read') : $minutes.' '.__('minute read');
    }

    public function readTimeCount()
    {
        $minutes = round(str_word_count(strip_tags($this->body)) / 100);

        return intval($minutes);
    }

    public function getReadableDateAttribute()
    {
        if (Carbon::now() > $this->created_at->addDays(7)) {
            $readableDate = $this->created_at->toFormattedDateString();
        } else {
            $readableDate = $this->created_at->diffForHumans();
        }

        return $readableDate;
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true)->published();
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeNotPinned(Builder $query): Builder
    {
        return $query->where('is_pinned', false);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now()->format('Y-m-d H:i:s'));
    }

    public function scopeNotPublished(Builder $query): Builder
    {
        return $query->whereNull('published_at');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->published()->whereNotNull('approved_at');
    }

    public function featuredImage(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')->where('collection_name', '=', 'featured-image');
    }

    public function storyAudio(): MorphOne
    {
        return $this->morphOne(Media::class, 'model')->where('collection_name', '=', 'story-audio');
    }
}
