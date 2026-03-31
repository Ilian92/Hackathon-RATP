<?php

namespace Database\Factories;

use App\Models\Gratification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gratification>
 */
class GratificationFactory extends Factory
{
    private static array $reasons = [
        'Excellent taux de satisfaction client',
        'Zéro incident sur le trimestre',
        'Ponctualité exemplaire',
        'Comportement exemplaire signalé par un usager',
        'Participation aux formations volontaires',
        'Ancienneté — 5 ans de service',
        'Ancienneté — 10 ans de service',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->chauffeur(),
            'amount' => fake()->randomElement([50, 100, 150, 200, 300, 500]),
            'reason' => fake()->randomElement(self::$reasons),
            'awarded_at' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
        ];
    }
}
