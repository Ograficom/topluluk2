<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityCardResource extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'avatar_url' => $this->getAvatar(),
            'stories_count' => $this->whenCounted('stories'),
            'members_count' => $this->whenCounted('followers'),
            'created_at' => DateTimeResource::make($this->created_at),
            'is_followed' => auth()->check() ? $this->isFollowedBy(auth()->user()) : false,
        ];
    }
}
