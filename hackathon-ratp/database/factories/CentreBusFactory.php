<?php

namespace Database\Factories;

use App\Models\CentreBus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CentreBus>
 */
class CentreBusFactory extends Factory
{
    private static array $depots = [
        'Dépôt de Lagny', 'Dépôt de Croissy-Beaubourg', 'Dépôt de Thiais',
        'Dépôt de Clamart', 'Dépôt de Nanterre', 'Dépôt de Saint-Denis',
        'Dépôt de Montrouge', 'Dépôt de Vitry', 'Dépôt de Pleyel',
        'Dépôt de Massy', 'Dépôt de Pavillons-sous-Bois', 'Dépôt de Versailles',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(self::$depots),
            'address' => fake()->streetAddress().', '.fake()->city(),
        ];
    }
}
