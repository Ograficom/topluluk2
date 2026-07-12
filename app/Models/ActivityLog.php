<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $guarded = [];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function actionLabel(): string
    {
        return self::actionLabelStatic($this->action);
    }

    public static function actionLabelStatic(string $action): string
    {
        return match ($action) {
            'login' => __('Logged in'),
            'logout' => __('Logged out'),
            'story_created' => __('Created a story'),
            'story_published' => __('Published a story'),
            'story_updated' => __('Updated a story'),
            'story_deleted' => __('Deleted a story'),
            'story_reposted' => __('Reposted a story'),
            'comment_created' => __('Posted a comment'),
            'comment_deleted' => __('Deleted a comment'),
            'follow_user' => __('Followed a user'),
            'unfollow_user' => __('Unfollowed a user'),
            'follow_community' => __('Joined a community'),
            'unfollow_community' => __('Left a community'),
            'like_story' => __('Liked a story'),
            'unlike_story' => __('Unliked a story'),
            'favorite_story' => __('Favorited a story'),
            'unfavorite_story' => __('Unfavorited a story'),
            'kyc_submitted' => __('Submitted KYC documents'),
            'kyc_verified' => __('KYC verified'),
            'kyc_rejected' => __('KYC rejected'),
            'message_sent' => __('Sent a message'),
            'profile_updated' => __('Updated profile'),
            'avatar_updated' => __('Updated avatar'),
            'cover_updated' => __('Updated cover image'),
            'settings_updated' => __('Updated settings'),
            'block_user' => __('Blocked a user'),
            'unblock_user' => __('Unblocked a user'),
            'report_submitted' => __('Submitted a report'),
            'community_created' => __('Created a community'),
            'poll_voted' => __('Voted in a poll'),
            default => __($action),
        };
    }
}
