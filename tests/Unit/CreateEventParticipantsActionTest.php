<?php

namespace Tests\Unit;

use App\Actions\CreateEventParticipantsAction;
use App\Models\Answer;
use App\Models\Event;
use App\Models\EventOption;
use App\Models\Participant;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEventParticipantsActionTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function can_create_participants_with_responses_through_action(): void
    {
        $name = 'Alex';
        $event = Event::factory()->create();

        $optionA = EventOption::factory()->for($event)->create();
        $optionB = EventOption::factory()->for($event)->create();
        $optionC = EventOption::factory()->for($event)->create();

        $payload = [
            [
                'event_option_uuid' => $optionA->uuid,
                'answer' => Answer::YES->value,
            ],
            [
                'event_option_uuid' => $optionB->uuid,
                'answer' => Answer::NOT_SURE->value,
            ], [
                'event_option_uuid' => $optionC->uuid,
                'answer' => Answer::NO->value,
            ],
        ];

        app(CreateEventParticipantsAction::class)->handle($event, $name, $payload);

        $participant = Participant::first();

        $this->assertSame($name, $participant->name);

        $responses = $participant->responses;

        $this->assertCount(count($payload), $responses);

        $responseForOptionA = $responses->where('event_option_id', $optionA->id)->first();

        $this->assertSame($responseForOptionA->answer, Answer::YES->value);

        $responseForOptionB = $responses->where('event_option_id', $optionB->id)->first();

        $this->assertSame($responseForOptionB->answer, Answer::NOT_SURE->value);

        $responseForOptionC = $responses->where('event_option_id', $optionC->id)->first();

        $this->assertSame($responseForOptionC->answer, Answer::NO->value);
    }
}
