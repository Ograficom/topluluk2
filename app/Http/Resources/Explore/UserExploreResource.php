<?php

namespace App\Http\Resources\Explore;

use App\Http\Resources\BadgeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserExploreResource extends JsonResource
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
            'avatar_url' => $this->getAvatar(),
            'primary_badge' => BadgeResource::make($this->whenLoaded('primaryBadge', $this->primaryBadge?->badge)),
            'followers_count' => $this->whenCounted('followers'),
            'is_followed' => auth()->check() ? $this->isFollowedBy(auth()->user()) : false,
        ];
    }
}
