<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'comment' => $this->comment,
            'media_url' => $this->getMedia(),
            'media_type' => $this->getMediaMimeType(),
            'replies' => self::collection($this->replies),
            'user' => UserResource::make($this->user),
            'created_at' => DateTimeResource::make($this->created_at),
            'likers_count' => $this->likers_count,
            'favoriters_count' => $this->favoriters_count,
            'isDeleted' => isset($this->deleted_at) ? true : false,
            'isBookmarkedBy' => auth()->check() ? $this->isFavoritedBy(auth()->user()) : null,
            'isLikedBy' => auth()->check() ? $this->isLikedBy(auth()->user()) : null,
            'isPostAuthor' => $this->isPostAuthor(),
            'user_can' => [
                'pin_top' => auth()->user()?->can('pinTop', $this->resource),
                'report' => auth()->user()?->can('report', $this->resource),
                'create' => auth()->user()?->can('create', $this->resource),
                'edit' => auth()->user()?->can('edit', $this->resource),
                'delete' => auth()->user()?->can('delete', $this->resource),
            ],
        ];
    }
}
