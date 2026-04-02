<?php

namespace App\Enums;

enum MissionMoucheDecision: string
{
    case Cloture = 'Cloture';
    case Sanctionne = 'Sanctionne';

    public function label(): string
    {
        return match ($this) {
            self::Cloture => 'Classé sans suite',
            self::Sanctionne => 'Sanction appliquée',
        };
    }
}
