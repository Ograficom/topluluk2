<?php

namespace App\Http\Resources\Explore;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityExploreResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'avatar_url' => $this->getAvatar(),
            'followers_count' => $this->whenCounted('followers'),
            'is_followed' => auth()->check() ? $this->isFollowedBy(auth()->user()) : false,
        ];
    }
}
