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
            'uuid' => $this->uuid,
            'date_time' => Date::toIsoString($this->date_time),
            'summary' => [
                'yes_count' => $this->yes_count,
                'not_sure_count' => $this->not_sure_count,
                'no_count' => $this->no_count,
            ],
        ];
    }
}
