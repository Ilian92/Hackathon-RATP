<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\Severity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Severity>
 */
class SeverityFactory extends Factory
{
    private static array $justifications = [
        'Comportement avéré après vérification des caméras embarquées.',
        'Témoignages concordants de plusieurs usagers présents.',
        'Incident isolé sans antécédent similaire sur ce chauffeur.',
        'Récurrence constatée — troisième signalement du même type en 6 mois.',
        'Infractions au code de la route confirmées par le système de géolocalisation.',
        'Impact limité sur les usagers, aucune blessure signalée.',
        'Mise en danger directe d\'usagers vulnérables.',
        'Non-respect répété des consignes de sécurité.',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'user_id' => User::factory()->role(UserRole::Com),
            'level' => fake()->numberBetween(0, 4),
            'justification' => fake()->randomElement(self::$justifications),
        ];
    }
}
