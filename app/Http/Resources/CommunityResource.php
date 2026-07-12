<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityResource extends JsonResource
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
            'cover_url' => $this->getCoverImage(),
            'rules' => $this->rules,
            'user' => UserResource::make($this->whenLoaded('user')),
            'user_can' => [
                'edit' => auth()->user()?->can('edit', $this->resource),
            ],
            'stories_count' => (int) $this->whenCounted('stories'),
            'members_count' => (int) $this->whenCounted('followables'),
            'total_views_count' => (int) $this->whenCounted('total_views_count'),
            'created_at' => DateTimeResource::make($this->created_at),
            'is_followed' => auth()->check() ? $this->isFollowedBy(auth()->user()) : false,
        ];
    }
}
