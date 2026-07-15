<?php

namespace App\Http\Resources;

use App\Support\Date;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'memo' => $this->memo,
            'expired_at' => Date::toIsoString($this->expired_at),
            'created_at' => Date::toIsoString($this->created_at),
            'updated_at' => Date::toIsoString($this->updated_at),
            'options' => EventOptionResource::collection($this->whenLoaded('options')),
            'participants' => ParticipantResource::collection($this->whenLoaded('participants')),
        ];
    }
}
