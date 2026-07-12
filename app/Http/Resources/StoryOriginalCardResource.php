<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryOriginalCardResource extends JsonResource
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
            'title' => html_entity_decode($this->title, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8'),
            'subtitle' => html_entity_decode($this->subtitle, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8'),
            'slug' => $this->slug,
            'type' => $this->type,
            'summary' => $this->getSummary(),
            'original_post' => new self($this->originalPost),
            'media_url' => $this->getFirstMediaUrl() ?? null,
            'community' => CommunityStoryCardResource::make($this->whenLoaded('community')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'user_can' => [
                'report' => auth()->user()?->can('report', $this->resource),
                'pin' => auth()->user()?->can('pin', $this->resource),
                'edit' => auth()->user()?->can('edit', $this->resource),
                'delete' => auth()->user()?->can('delete', $this->resource),
            ],
            'created_at' => DateTimeResource::make($this->published_at),
            'isNSFW' => (bool) $this->is_nsfw,
        ];
    }
}
