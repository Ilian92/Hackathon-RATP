<?php

namespace App\Enums;

enum UserStatus: string
{
    case Actif = 'Actif';
    case EnVacances = 'EnVacances';
    case EnFormation = 'EnFormation';
    case Suspendu = 'Suspendu';
    case Retraite = 'Retraite';

    public function label(): string
    {
        return match ($this) {
            self::Actif => 'Actif',
            self::EnVacances => 'En vacances',
            self::EnFormation => 'En formation',
            self::Suspendu => 'Suspendu',
            self::Retraite => 'Retraité',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Actif => 'green',
            self::EnVacances => 'blue',
            self::EnFormation => 'yellow',
            self::Suspendu => 'red',
            self::Retraite => 'gray',
        };
    }
}
