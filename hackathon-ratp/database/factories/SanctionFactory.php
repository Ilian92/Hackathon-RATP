<?php

namespace Database\Factories;

use App\Models\Sanction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sanction>
 */
class SanctionFactory extends Factory
{
    private static array $types = [
        'Avertissement',
        'Blâme',
        'Mise à pied',
        'Rétrogradation',
    ];

    private static array $descriptions = [
        'Retards répétés non justifiés',
        'Non-respect du règlement intérieur',
        'Comportement inapproprié envers un usager',
        'Absence injustifiée',
        'Utilisation du téléphone pendant la conduite',
        'Non-respect des consignes de sécurité',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->chauffeur(),
            'type' => fake()->randomElement(self::$types),
            'description' => fake()->randomElement(self::$descriptions),
            'sanctioned_at' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
        ];
    }
}
