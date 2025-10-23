<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'user' => new UserResource($this->whenLoaded('user')),
            'haircut' => new HaircutResource($this->whenLoaded('haircut')),
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}