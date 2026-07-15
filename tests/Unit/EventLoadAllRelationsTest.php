<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\Event;
use App\Models\EventOption;
use App\Models\Participant;
use App\Models\Response;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventLoadAllRelationsTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function all_event_relations_are_loaded(): void
    {
        $event = Event::factory()->create();

        $optionA = EventOption::factory()
            ->for($event)
            ->create();

        $optionB = EventOption::factory()
            ->for($event)
            ->create();

        $participantA = Participant::factory()
            ->for($event)
            ->create();

        $participantB = Participant::factory()
            ->for($event)
            ->create();

        Response::factory()->create([
            'participant_id' => $participantA->id,
            'event_option_id' => $optionA->id,
            'answer' => Answer::YES->value,
        ]);

        Response::factory()->create([
            'participant_id' => $participantA->id,
            'event_option_id' => $optionB->id,
            'answer' => Answer::NO->value,
        ]);

        Response::factory()->create([
            'participant_id' => $participantB->id,
            'event_option_id' => $optionA->id,
            'answer' => Answer::NOT_SURE->value,
        ]);

        $event->loadAllRelations();

        $this->assertTrue($event->relationLoaded('options'));
        $this->assertTrue($event->relationLoaded('participants'));

        foreach ($event->participants as $participant) {
            $this->assertTrue(
                $participant->relationLoaded('responses')
            );
        }
    }

    #[Test]
    public function response_counts_are_loaded_for_each_option(): void
    {
        $event = Event::factory()->create();

        $optionA = EventOption::factory()
            ->for($event)
            ->create();

        $optionB = EventOption::factory()
            ->for($event)
            ->create();

        $participants = Participant::factory()
            ->for($event)
            ->count(4)
            ->create();

        Response::factory()->create([
            'participant_id' => $participants[0]->id,
            'event_option_id' => $optionA->id,
            'answer' => Answer::YES->value,
        ]);

        Response::factory()->create([
            'participant_id' => $participants[1]->id,
            'event_option_id' => $optionA->id,
            'answer' => Answer::YES->value,
        ]);

        Response::factory()->create([
            'participant_id' => $participants[2]->id,
            'event_option_id' => $optionA->id,
            'answer' => Answer::NOT_SURE->value,
        ]);

        Response::factory()->create([
            'participant_id' => $participants[3]->id,
            'event_option_id' => $optionA->id,
            'answer' => Answer::NO->value,
        ]);

        Response::factory()->create([
            'participant_id' => $participants[0]->id,
            'event_option_id' => $optionB->id,
            'answer' => Answer::NO->value,
        ]);

        Response::factory()->create([
            'participant_id' => $participants[1]->id,
            'event_option_id' => $optionB->id,
            'answer' => Answer::NO->value,
        ]);

        $event->loadAllRelations();

        $loadedOptionA = $event->options->firstWhere(
            'id',
            $optionA->id
        );

        $loadedOptionB = $event->options->firstWhere(
            'id',
            $optionB->id
        );

        $this->assertNotNull($loadedOptionA);
        $this->assertNotNull($loadedOptionB);

        $this->assertSame(2, $loadedOptionA->yes_count);
        $this->assertSame(1, $loadedOptionA->not_sure_count);
        $this->assertSame(1, $loadedOptionA->no_count);

        $this->assertSame(0, $loadedOptionB->yes_count);
        $this->assertSame(0, $loadedOptionB->not_sure_count);
        $this->assertSame(2, $loadedOptionB->no_count);
    }

    #[Test]
    public function it_returns_the_same_event_instance(): void
    {
        $event = Event::factory()->create();

        $loadedEvent = $event->loadAllRelations();

        $this->assertSame($event, $loadedEvent);
    }

    #[Test]
    public function participant_responses_are_scoped_to_the_participant(): void
    {
        $event = Event::factory()->create();

        $option = EventOption::factory()
            ->for($event)
            ->create();

        $participantA = Participant::factory()
            ->for($event)
            ->create();

        $participantB = Participant::factory()
            ->for($event)
            ->create();

        $responseA = Response::factory()->create([
            'participant_id' => $participantA->id,
            'event_option_id' => $option->id,
            'answer' => Answer::YES->value,
        ]);

        $responseB = Response::factory()->create([
            'participant_id' => $participantB->id,
            'event_option_id' => $option->id,
            'answer' => Answer::NO->value,
        ]);

        $event->loadAllRelations();

        $loadedParticipantA = $event->participants->firstWhere(
            'id',
            $participantA->id
        );

        $loadedParticipantB = $event->participants->firstWhere(
            'id',
            $participantB->id
        );

        $this->assertCount(1, $loadedParticipantA->responses);
        $this->assertCount(1, $loadedParticipantB->responses);

        $this->assertSame(
            $responseA->id,
            $loadedParticipantA->responses->first()->id
        );

        $this->assertSame(
            $responseB->id,
            $loadedParticipantB->responses->first()->id
        );
    }
}
