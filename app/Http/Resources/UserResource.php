<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'avatar_url' => $this->getAvatar(),
            'cover_url' => $this->getCoverImage(),
            'rating' => $this->rating,
            'primary_badge' => BadgeResource::make($this->whenLoaded('primaryBadge', $this->primaryBadge?->badge)),
            'created_at' => DateTimeResource::make($this->created_at),
            'preference_settings' => $this->preference_settings,
            'isDeleted' => isset($this->deleted_at) ? true : false,
            // 'is_online' => $this->isOnline(),
            'user_can' => [
                'cp' => auth()->user()?->can('cp', $this->resource),
            ],
        ];
    }
}
