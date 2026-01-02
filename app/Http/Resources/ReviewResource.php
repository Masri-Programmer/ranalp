<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'profile_photo_url' => $this->user->profile_photo_url,
            ],
            'rating' => $this->rating,
            'body' => $this->body,
            'created_at' => $this->created_at,
            'time_ago' => $this->created_at->diffForHumans(),
            'can_edit' => Auth::id() === $this->user_id,
        ];
    }
}
