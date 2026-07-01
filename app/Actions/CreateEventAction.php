<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\EventOption;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CreateEventAction
{
    public function handle(array $eventInfo): Event
    {
        return DB::transaction(function () use ($eventInfo) {
            $event = Event::create([
                'title' => $eventInfo['title'],
                'memo' => $eventInfo['memo'],
                'expired_at' => $this->getExpiredAt($eventInfo['date_times']),
            ]);

            collect($eventInfo['date_times'])->each(fn (string $dateTime) => EventOption::create([
                'date_time' => $dateTime,
                'event_id' => $event->id,
            ]));

            return $event;
        });
    }

    private function getExpiredAt(array $dateTimes): string
    {
        return collect($dateTimes)
            ->map(fn (string $dateTime) => CarbonImmutable::parse($dateTime)->utc())
            ->max()
            ->addWeek()
            ->toISOString();
    }
}
