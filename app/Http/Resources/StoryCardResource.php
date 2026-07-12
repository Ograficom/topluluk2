<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryCardResource extends JsonResource
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
            'slug' => $this->slug,
            'type' => $this->type,
            'first_paragraph' => $this->getFirstParagraph(),
            'media_url' => $this->getFirstMediaUrl() ?? null,
            'original_post' => new StoryOriginalCardResource($this->originalPost),
            'user_can' => [
                'report' => auth()->user()?->can('report', $this->resource),
                'pin' => auth()->user()?->can('pin', $this->resource),
                'edit' => auth()->user()?->can('edit', $this->resource),
                'delete' => auth()->user()?->can('delete', $this->resource),
            ],
            'participants' => UserCommentParticipantsResource::collection($this->whenLoaded('participants')),
            'community' => CommunityStoryCardResource::make($this->whenLoaded('community')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => DateTimeResource::make($this->isPublished() ? $this->published_at : $this->created_at),
            'comments_count' => $this->all_comments_count,
            'likers_count' => $this->likers_count,
            'favoriters_count' => $this->favoriters_count,
            'isNSFW' => (bool) $this->is_nsfw,
            'isPublished' => $this->isPublished(),
            'isPinned' => auth()->check() ? $this->isPinned() : null,
            'isSavedBy' => auth()->check() ? $this->isFavoritedBy(auth()->user()) : null,
            'isLikedBy' => auth()->check() ? $this->isLikedBy(auth()->user()) : false,
            'views_count' => $this->views_count,
        ];
    }
}
