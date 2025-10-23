<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HaircutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'featured_image_url' => $this->featured_image_url,
            'is_published' => $this->is_published,
            'like_count' => $this->like_count,
            'favorite_count' => $this->favorite_count,
            'average_rating' => $this->when(
                $this->reviews_avg_rating !== null, 
                round($this->reviews_avg_rating, 1)
            ),
            'reviews_count' => $this->whenLoaded('reviews', function () {
                return $this->reviews_count;
            }, 0),
            'likes_count' => $this->whenLoaded('likes', function () {
                return $this->likes_count;
            }, 0),
            'admin' => new UserResource($this->whenLoaded('admin')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'images' => HaircutImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}