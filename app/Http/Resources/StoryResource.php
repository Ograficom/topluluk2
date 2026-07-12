<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
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
            'content' => $this->body ? json_decode($this->body, true)['blocks'] : null,
            'original_post' => StoryOriginalCardResource::make($this->whenLoaded('originalPost')),
            'user_can' => [
                'report' => auth()->user()?->can('report', $this->resource),
                'pin' => auth()->user()?->can('pin', $this->resource),
                'edit' => auth()->user()?->can('edit', $this->resource),
                'delete' => auth()->user()?->can('delete', $this->resource),
            ],
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'comment' => CommentResource::make($this->whenLoaded('comment')),
            'community' => CommunityStoryCardResource::make($this->whenLoaded('community')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => DateTimeResource::make($this->isPublished() ? $this->published_at : $this->created_at),
            'comments_count' => $this->whenCounted('allComments'),
            'views_count' => $this->views_count,
            'likers_count' => $this->likers_count,
            'favoriters_count' => $this->favoriters_count,
            'reposts_count' => $this->reposts->count(),
            'isCommentsDisabled' => $this->isCommentsDisabled(),
            'isNSFW' => (bool) $this->is_nsfw,
            'isPublished' => $this->isPublished(),
            'isRepostedBy' => auth()->check() ? $this->isRepostedBy(auth()->user()) : false,
            'isPinned' => auth()->check() ? $this->isPinned() : null,
            'isSavedBy' => auth()->check() ? $this->isFavoritedBy(auth()->user()) : null,
            'isLikedBy' => auth()->check() ? $this->isLikedBy(auth()->user()) : false,
        ];
    }
}
