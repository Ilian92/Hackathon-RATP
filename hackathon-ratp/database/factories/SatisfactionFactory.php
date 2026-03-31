<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Satisfaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Satisfaction>
 */
class SatisfactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'note' => fake()->numberBetween(0, 10),
            'description' => fake()->optional(0.6)->sentence(),
            'client_id' => Client::factory(),
            'user_id' => User::factory()->chauffeur(),
        ];
    }

    public function positive(): static
    {
        return $this->state(fn (array $attributes) => [
            'note' => fake()->numberBetween(7, 10),
        ]);
    }

    public function negative(): static
    {
        return $this->state(fn (array $attributes) => [
            'note' => fake()->numberBetween(0, 4),
        ]);
    }
}
