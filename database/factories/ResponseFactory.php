<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\EventOption;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'answer' => fake()->randomElement(Answer::cases()),
            'participant_id' => Participant::factory(),
            'event_option_id' => EventOption::factory(),
        ];

    }
}
