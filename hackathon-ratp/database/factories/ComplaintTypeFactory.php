<?php

namespace Database\Factories;

use App\Models\ComplaintType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintType>
 */
class ComplaintTypeFactory extends Factory
{
    private static array $types = [
        'Comportement du chauffeur',
        'Retard',
        'Propreté du véhicule',
        'Problème technique',
        'Agression / Incivilité',
        'Accessibilité',
        'Tarification',
        'Autre',
    ];

    private static int $index = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = self::$types[self::$index % count(self::$types)];
        self::$index++;

        return [
            'name' => $name,
        ];
    }
}
