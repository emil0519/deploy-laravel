<?php

namespace Tests\Feature;

use App\Actions\CreateEventAction;
use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEventTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function can_create_an_event(): void
    {
        $this->withoutExceptionHandling();

        $payload = [
            'title' => 'Board game Event',
            'memo' => 'Anyone is free to join, starts from July, please pick a date.',
            'date_times' => [
                now()->addDay()->utc()->startOfSecond()->format('Y-m-d\TH:i:s\Z'),
                now()->addDays(2)->utc()->startOfSecond()->format('Y-m-d\TH:i:s\Z'),
                now()->addWeek()->utc()->startOfSecond()->format('Y-m-d\TH:i:s\Z'),
            ],
        ];

        $event = Event::factory()->make([
            'title' => $payload['title'],
            'memo' => $payload['memo'],
        ]);

        $this->mock(CreateEventAction::class, function ($mock) use ($payload, $event) {
            $mock->shouldReceive('handle')
                ->once()
                ->with($payload)
                ->andReturn($event);
        });

        $response = $this->postJson('/api/events', $payload);

        $response
            ->assertCreated();

        $this->assertNotNull($response->json('data'));

        // TODO: verify full EventResource response shape.
    }

    #[Test]
    public function title_is_required(): void
    {
        $this->assertFieldIsInvalid(['title' => null]);
    }

    #[Test]
    public function title_must_be_a_string(): void
    {
        $this->assertFieldIsInvalid(['title' => ['not-a-string']]);
    }

    #[Test]
    public function title_must_not_be_longer_than_255_characters(): void
    {
        $this->assertFieldIsInvalid(['title' => str_repeat('a', 256)]);
    }

    #[Test]
    public function memo_is_required(): void
    {
        $this->assertFieldIsInvalid(['memo' => null]);
    }

    #[Test]
    public function memo_must_be_a_string(): void
    {
        $this->assertFieldIsInvalid(['memo' => ['not-a-string']]);
    }

    #[Test]
    public function date_times_is_required(): void
    {
        $this->assertFieldIsInvalid(['date_times' => null]);
    }

    #[Test]
    public function date_times_must_be_an_array(): void
    {
        $this->assertFieldIsInvalid(['date_times' => '2026-07-01T10:00:00Z']);
    }

    #[Test]
    public function date_times_must_have_at_least_one_item(): void
    {
        $this->assertFieldIsInvalid(['date_times' => []]);
    }

    #[Test]
    public function date_time_item_is_required(): void
    {
        $this->assertFieldIsInvalid(
            ['date_times' => [null]],
            'date_times.0'
        );
    }

    #[Test]
    public function date_time_item_must_match_utc_format(): void
    {
        $this->assertFieldIsInvalid(
            ['date_times' => ['2026-07-01 10:00:00']],
            'date_times.0'
        );
    }

    #[Test]
    public function date_time_item_must_be_utc_z_format(): void
    {
        $this->assertFieldIsInvalid(
            ['date_times' => ['2026-07-01T10:00:00+08:00']],
            'date_times.0'
        );
    }

    private function assertFieldIsInvalid(array $field, ?string $errorKey = null): void
    {
        $valid = [
            'title' => 'Board game Event',
            'memo' => 'Anyone is free to join, starts from July, please pick a date.',
            'date_times' => [
                now()->addDay()->utc()->startOfSecond()->format('Y-m-d\TH:i:s\Z'),
            ],
        ];

        $response = $this->postJson(
            '/api/events',
            array_merge($valid, $field)
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors($errorKey ?? array_key_first($field));
    }
}
