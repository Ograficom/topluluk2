<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentCardResource extends JsonResource
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
            'story_id' => $this->commentable_id,
            'comment' => $this->comment,
            'media_url' => $this->getMedia(),
            'media_type' => $this->getMediaMimeType(),
            'story' => StoryCardResource::make($this->whenLoaded('story')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => DateTimeResource::make($this->created_at),
            'likers_count' => $this->likers_count,
            'isBookmarkedBy' => auth()->check() ? $this->isFavoritedBy(auth()->user()) : null,
            'isLikedBy' => auth()->check() ? $this->isLikedBy(auth()->user()) : null,
        ];
    }
}
