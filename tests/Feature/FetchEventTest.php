<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FetchEventTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function can_fetch_specific_event(): void
    {
        $this->withoutExceptionHandling();

        $event = Event::factory()->create();

        $response = $this->getJson("api/events/{$event->uuid}");

        $response->assertSuccessful();

        $this->assertNotNull($response->json('data'));
    }
}
