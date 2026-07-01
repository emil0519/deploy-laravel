<?php

namespace Database\Factories;

use App\Models\EventOptions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventOptions>
 */
class EventOptionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => EventOptions::factory(),
            'date_time' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
