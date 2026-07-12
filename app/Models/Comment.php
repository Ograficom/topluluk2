<?php

namespace App\Models;

use App\Models\Traits\Favoriteable;
use App\Models\Traits\Likeable;
use App\Services\CommentMentionExtractorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Casts\CleanHtmlInput;

class Comment extends Model
{
    use Favoriteable;
    use Likeable;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'comment',
        'parent_id',
        'commentable_id',
        'commentable_type',
        'spam_reports',
    ];

    protected $casts = [
        'comment' => CleanHtmlInput::class,
    ];

    // Event hook
    public static function boot()
    {
        parent::boot();

        static::created(function (Comment $comment) {
            $comment->mentions()->createMany(
                (new CommentMentionExtractorService($comment->comment))->getMentionEntities()
            );
        });
    }

    public function getLikersCountAttribute()
    {
        return $this->likers()->count();
    }

    public function getFavoritersCountAttribute()
    {
        return $this->favoriters()->count();
    }

    public function isReply()
    {
        return $this->parent_id !== null;
    }

    public function scopeParent(Builder $builder)
    {
        $builder->whereNull('parent_id');
    }

    public function parentComment()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function story()
    {
        return $this->belongsTo(Story::class, 'commentable_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')->withTrashed();
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    public function mentions()
    {
        return $this->hasMany(CommentMention::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function getMedia()
    {
        if (! $this->media_path) {
            return null;
        }

        return Storage::disk(getCurrentDisk())->url($this->media_path);
    }

    public function getMediaMimeType(): ?string
    {
        return str_contains($this->media_mime_type, 'video/')
            ? 'video'
            : (str_contains($this->media_mime_type, 'image/')
                ? 'image'
                : null);
    }

    public function isPostAuthor()
    {
        return $this->story->user_id == $this->user_id;
    }
}
