<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'author' => new UserResource($this->whenLoaded('author')),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}