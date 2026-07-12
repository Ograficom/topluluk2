<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'avatar_url' => $this->getAvatar(),
            'cover_url' => $this->getCoverImage(),
            'rating' => $this->rating,
            'profile' => UserProfileSettingsResource::make($this->whenLoaded('profile')),
            'primary_badge' => BadgeResource::make($this->whenLoaded('primaryBadge', $this->primaryBadge?->badge)),
            'user_can' => [
                'follow' => auth()->user()?->can('follow', $this->resource),
                'ban' => auth()->user()?->can('ban', $this->resource),
                'block' => auth()->user()?->can('block', $this->resource),
                'report' => auth()->user()?->can('report', $this->resource),
                'edit' => auth()->user()?->can('edit', $this->resource),
                'delete' => auth()->user()?->can('delete', $this->resource),
            ],
            'created_at' => DateTimeResource::make($this->created_at),
            'preference_settings' => $this->preference_settings,
            // 'is_online' => $this->isOnline(),
            // 'posts_count' => $this->posts_count,
            // 'comments_count' => $this->comments_count,
            // 'notifications_count' => $this->unreadNotifications()->count(),
            'followables_count' => $this->whenCounted('followables'),
            'followings_count' => $this->whenCounted('followings'),
            'badges' => UserBadgeResource::collection($this->whenLoaded('badges', $this->badges->sortBy('sort_id'))),
            'is_followed' => auth()->check() ? $this->whenLoaded('followables', $this->followables->pluck('user_id')->contains(auth()->user()?->id)) : null,
            // 'is_followed' => auth()->check() ? $this->isFollowedBy(auth()->user()) : false,
            'is_blocked' => auth()->check() ? $this->isBlockedBy(auth()->user()) : false,
            'is_blocking' => auth()->check() ? $this->isBlocking(auth()->user()) : false,
            'isSuspended' => $this->isSuspended(),
        ];
    }
}
