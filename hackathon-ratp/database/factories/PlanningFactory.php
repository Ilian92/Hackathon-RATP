<?php

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Planning;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Planning>
 */
class PlanningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bus_id' => Bus::factory(),
            'user_id' => User::factory()->chauffeur(),
            'date' => fake()->dateTimeBetween('-3 months', '+1 month')->format('Y-m-d'),
        ];
    }
}
