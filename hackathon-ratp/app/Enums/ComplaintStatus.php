<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case EnCours = 'EnCours';
    case Clos = 'Clos';
    case Abouti = 'Abouti';

    public function label(): string
    {
        return match ($this) {
            self::EnCours => 'En cours',
            self::Clos => 'Clos',
            self::Abouti => 'Abouti',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EnCours => 'yellow',
            self::Clos => 'gray',
            self::Abouti => 'red',
        };
    }
}
