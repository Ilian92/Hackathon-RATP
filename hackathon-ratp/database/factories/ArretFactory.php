<?php

namespace Database\Factories;

use App\Models\Arret;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Arret>
 */
class ArretFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Zone Île-de-France (bounding box approx.)
        return [
            'nom' => fake()->streetName(),
            'latitude' => fake()->randomFloat(7, 48.6, 49.1),
            'longitude' => fake()->randomFloat(7, 2.0, 2.8),
        ];
    }
}
