<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSimpleResource extends JsonResource
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
            'avatar_url' => $this->getAvatar(),
            'rating' => $this->rating,
            'isDeleted' => isset($this->deleted_at) ? true : false,
        ];
    }
}
