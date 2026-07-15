<?php

namespace Tests\Feature;

use App\Actions\CreateEventParticipantsAction;
use App\Models\Answer;
use App\Models\Event;
use App\Models\EventOption;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Response;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateParticipantResponsesTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function can_create_a_participant_with_responses(): void
    {
        $event = Event::factory()->create();

        $optionA = EventOption::factory()->for($event)->create();
        $optionB = EventOption::factory()->for($event)->create();
        $optionC = EventOption::factory()->for($event)->create();

        $payload = [
            'participant_name' => 'Alex',
            'responses' => [
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
            ],
        ];

        $this->mock(CreateEventParticipantsAction::class, function ($mock) use ($event, $payload) {
            $mock->shouldReceive('handle')
                ->once()
                ->with(
                    Mockery::on(
                        fn (Event $argument) => $argument->is($event)
                    ),
                    $payload['participant_name'],
                    $payload['responses'],
                )
                ->andReturn($event);
        });

        $response = $this->postJson("/api/events/{$event->uuid}/participants", $payload);

        $response->assertCreated();

        $this->assertSame($event->uuid, $response->json('data.uuid'));

    }

    #[Test]
    public function participant_name_is_required(): void
    {
        $this->assertFieldIsInvalid([
            'participant_name' => null,
        ]);
    }

    #[Test]
    public function participant_name_must_be_a_string(): void
    {
        $this->assertFieldIsInvalid([
            'participant_name' => ['Alex'],
        ]);
    }

    #[Test]
    public function participant_name_must_not_exceed_255_characters(): void
    {
        $this->assertFieldIsInvalid([
            'participant_name' => str_repeat('a', 256),
        ]);
    }

    #[Test]
    public function responses_are_required(): void
    {
        $this->assertFieldIsInvalid([
            'responses' => null,
        ]);
    }

    #[Test]
    public function responses_must_be_an_array(): void
    {
        $this->assertFieldIsInvalid([
            'responses' => 'invalid',
        ]);
    }

    #[Test]
    public function responses_must_contain_at_least_one_item(): void
    {
        $this->assertFieldIsInvalid([
            'responses' => [],
        ]);
    }

    #[Test]
    public function event_option_uuid_is_required(): void
    {
        $this->assertFieldIsInvalid(
            [
                'responses' => [
                    [
                        'answer' => Answer::YES->value,
                    ],
                ],
            ],
            'responses.0.event_option_uuid',
        );
    }

    #[Test]
    public function event_option_uuid_must_be_distinct(): void
    {
        $this->assertFieldIsInvalidUsing(
            function (
                Event $event,
                EventOption $option,
                array $valid,
            ): array {
                return [
                    ...$valid,
                    'responses' => [
                        [
                            'event_option_uuid' => $option->uuid,
                            'answer' => Answer::YES->value,
                        ],
                        [
                            'event_option_uuid' => $option->uuid,
                            'answer' => Answer::NO->value,
                        ],
                    ],
                ];
            },
            'responses.1.event_option_uuid',
        );
    }

    #[Test]
    public function event_option_uuid_must_exist_for_the_event(): void
    {
        $this->assertFieldIsInvalidUsing(
            function (
                Event $event,
                EventOption $option,
                array $valid,
            ): array {
                $otherEvent = Event::factory()->create();

                $foreignOption = EventOption::factory()
                    ->for($otherEvent)
                    ->create();

                return [
                    ...$valid,
                    'responses' => [
                        [
                            'event_option_uuid' => $foreignOption->uuid,
                            'answer' => Answer::YES->value,
                        ],
                    ],
                ];
            },
            'responses.0.event_option_uuid',
        );
    }

    #[Test]
    public function response_answer_is_required(): void
    {
        $this->assertFieldIsInvalidUsing(
            function (
                Event $event,
                EventOption $option,
                array $valid,
            ): array {
                return [
                    ...$valid,
                    'responses' => [
                        [
                            'event_option_uuid' => $option->uuid,
                        ],
                    ],
                ];
            },
            'responses.0.answer',
        );
    }

    #[Test]
    public function response_answer_must_be_a_valid_answer(): void
    {
        $this->assertFieldIsInvalidUsing(
            function (
                Event $event,
                EventOption $option,
                array $valid,
            ): array {
                return [
                    ...$valid,
                    'responses' => [
                        [
                            'event_option_uuid' => $option->uuid,
                            'answer' => 999,
                        ],
                    ],
                ];
            },
            'responses.0.answer',
        );
    }

    private function assertFieldIsInvalid(
        array $field,
        ?string $errorKey = null,
    ): void {
        $this->assertFieldIsInvalidUsing(
            fn (
                Event $event,
                EventOption $option,
                array $valid,
            ): array => array_merge($valid, $field),
            $errorKey ?? array_key_first($field),
        );
    }

    private function assertFieldIsInvalidUsing(
        callable $buildPayload,
        string $errorKey,
    ): void {
        $event = Event::factory()->create();

        $option = EventOption::factory()
            ->for($event)
            ->create();

        $valid = [
            'participant_name' => 'Alex',
            'responses' => [
                [
                    'event_option_uuid' => $option->uuid,
                    'answer' => Answer::YES->value,
                ],
            ],
        ];

        $this->mock(
            CreateEventParticipantsAction::class,
            function ($mock) {
                $mock->shouldNotReceive('handle');
            }
        );

        $payload = $buildPayload($event, $option, $valid);

        $response = $this->postJson(
            "/api/events/{$event->uuid}/participants",
            $payload,
        );

        $response->assertStatus(
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $response->assertJsonValidationErrors($errorKey);
    }
}
