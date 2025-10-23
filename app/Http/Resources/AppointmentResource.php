<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'haircut' => new HaircutResource($this->whenLoaded('haircut')),
            'messages' => AppointmentMessageResource::collection($this->whenLoaded('messages')),
            'created_at' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}