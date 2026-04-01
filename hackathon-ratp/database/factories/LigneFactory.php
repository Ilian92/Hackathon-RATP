<?php

namespace Database\Factories;

use App\Models\CentreBus;
use App\Models\Ligne;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ligne>
 */
class LigneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    private static array $lignes = [
        '21', '26', '38', '42', '48', '52', '57', '62', '66', '68',
        '74', '80', '85', '91', '92', '95', 'N01', 'N02', 'N11', 'N52',
        '100', '102', '115', '141', '144', '147', '150', '172', '183', '185',
    ];

    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->randomElement(self::$lignes),
            'centre_bus_id' => CentreBus::factory(),
        ];
    }
}
