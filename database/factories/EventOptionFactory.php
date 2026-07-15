<?php

namespace Database\Factories;

use App\Models\EventOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventOption>
 */
class EventOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => EventOption::factory(),
            'date_time' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
