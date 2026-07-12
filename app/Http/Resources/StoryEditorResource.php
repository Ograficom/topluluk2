<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryEditorResource extends JsonResource
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
            'title' => $this->title ?? '',
            'subtitle' => $this->subtitle ?? '',
            'slug' => $this->slug,
            'type' => $this->type,
            'body' => $this->body,
            'meta' => $this->meta,
            'user_can' => [
                'moderate' => auth()->user()?->can('moderate', $this->resource),
            ],
            'is_comments_disabled' => (bool) $this->is_comments_disabled,
            'is_pinned' => (bool) $this->is_pinned,
            'is_nsfw' => (bool) $this->is_nsfw,
            'is_published' => $this->isPublished(),
            'community' => CommunityStoryCardResource::make($this->whenLoaded('community')),
        ];
    }
}
