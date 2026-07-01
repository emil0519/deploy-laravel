<?php

namespace Tests\Unit;

use App\Actions\CreateEventAction;
use App\Models\EventOption;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEventActionTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function can_create_an_event_with_proper_date_time(): void
    {
        $title = 'Board game Event';
        $memo = 'Anyone is free to join, starts from July, please pick a date.';

        $eventInfo = [
            'title' => $title,
            'memo' => $memo,
            'date_times' => [
                now()->addDay()->utc()->startOfSecond()->toISOString(),
                now()->addDays(2)->utc()->startOfSecond()->toISOString(),
                now()->addWeek()->utc()->startOfSecond()->toISOString(),
            ],
        ];

        $event = app(CreateEventAction::class)->handle($eventInfo);

        $this->assertSame($event->title, $title);
        $this->assertSame($event->memo, $memo);

        $options = $event->options;

        $this->assertCount(3, $options);

        collect($eventInfo['date_times'])->each(function (string $dateTime, int $index) use ($options) {
            $this->assertSame(
                $dateTime,
                $options[$index]->date_time->utc()->toISOString()
            );
        });

        $expectedExpiredAt = collect($eventInfo['date_times'])
            ->map(fn (string $dateTime) => CarbonImmutable::parse($dateTime)->utc())
            ->max()
            ->addWeek()
            ->toISOString();

        $this->assertSame(
            $expectedExpiredAt,
            $event->expired_at->utc()->toISOString()
        );
    }

    #[Test]
    public function expired_at_is_based_on_latest_date_time_even_when_date_times_are_unsorted(): void
    {
        $latestDateTime = now()->addWeeks(3)->utc()->startOfSecond();

        $eventInfo = [
            'title' => 'Board game Event',
            'memo' => 'Anyone is free to join.',
            'date_times' => [
                now()->addDay()->utc()->startOfSecond()->toISOString(),
                $latestDateTime->toISOString(),
                now()->addWeek()->utc()->startOfSecond()->toISOString(),
            ],
        ];

        $event = app(CreateEventAction::class)->handle($eventInfo);

        $this->assertSame(
            $latestDateTime->addWeek()->toISOString(),
            $event->expired_at->utc()->toISOString()
        );
    }

    #[Test]
    public function all_event_options_belong_to_the_created_event(): void
    {
        $eventInfo = [
            'title' => 'Board game Event',
            'memo' => 'Anyone is free to join.',
            'date_times' => [
                now()->addDay()->utc()->startOfSecond()->toISOString(),
                now()->addDays(2)->utc()->startOfSecond()->toISOString(),
                now()->addDays(3)->utc()->startOfSecond()->toISOString(),
            ],
        ];

        $event = app(CreateEventAction::class)->handle($eventInfo);

        $this->assertCount(3, $event->options);

        $event->options->each(function (EventOption $option) use ($event) {
            $this->assertSame($event->id, $option->event_id);
        });
    }

    #[Test]
    public function it_fails_when_date_times_is_empty(): void
    {
        $this->expectException(\Throwable::class);

        app(CreateEventAction::class)->handle([
            'title' => 'Board game Event',
            'memo' => 'Anyone is free to join.',
            'date_times' => [],
        ]);

        $this->assertDatabaseCount('events', 0);
        $this->assertDatabaseCount('event_options', 0);
    }
}
