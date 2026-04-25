<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{

    /**
     * Define the model's default state.
     *   'name', 'sport_type', 'home_team', 'away_team',
     *   'home_odds', 'away_odds', 'draw_odds',
     *  'status', 'result', 'starts_at', 'ends_at',
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city() . ' FC',
            'sport_type' => 'Hockey',
            'home_team' => 'Jets',
            'away_team' => 'Canucks',
            'home_odds' => 1,
            'away_odds' => 0.5,
            'draw_odds' => 0.2,
            'status' => 'upcoming',
            'result' => null,
            'starts_at' => now()->addHours(rand(1, 48)),
            'ends_at' => now()->addHours(rand(48, 50)),
        ];
    }
}
