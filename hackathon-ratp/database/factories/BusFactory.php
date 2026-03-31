<?php

namespace Database\Factories;

use App\Models\Bus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bus>
 */
class BusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $letters = strtoupper(fake()->lexify('??'));
        $digits = fake()->numerify('###');
        $letters2 = strtoupper(fake()->lexify('??'));

        return [
            'code' => "{$letters}-{$digits}-{$letters2}",
        ];
    }
}
