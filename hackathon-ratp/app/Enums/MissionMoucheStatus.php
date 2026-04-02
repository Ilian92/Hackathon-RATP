<?php

namespace App\Enums;

enum MissionMoucheStatus: string
{
    case EnCours = 'EnCours';
    case Completee = 'Completee';
    case Decidee = 'Decidee';

    public function label(): string
    {
        return match ($this) {
            self::EnCours => 'En cours',
            self::Completee => 'Complétée',
            self::Decidee => 'Décidée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EnCours => 'blue',
            self::Completee => 'amber',
            self::Decidee => 'gray',
        };
    }
}
