<?php

namespace App\Http\Resources;

use App\Support\Date;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'date_time' => Date::toIsoString($this->date_time),
        ];
    }
}
