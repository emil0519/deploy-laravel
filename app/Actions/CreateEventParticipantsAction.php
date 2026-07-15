<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\EventOption;

class CreateEventParticipantsAction
{
    public function handle(Event $event, string $participantName, array $responses): Event
    {
        $participant = $event->participants()->create(['name' => $participantName]);

        collect($responses)->each(function (array $data) use ($participant) {
            $eventOption = EventOption::where('uuid', $data['event_option_uuid'])->firstOrFail();

            $eventOption->responses()->create([
                'answer' => $data['answer'],
                'participant_id' => $participant->id,
            ]);
        });

        return $event;
    }
}
