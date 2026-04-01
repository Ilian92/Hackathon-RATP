<?php

namespace App\Enums;

enum ComplaintStep: string
{
    case ComReview = 'ComReview';
    case ManagerReview = 'ManagerReview';
    case RHReview = 'RHReview';
    case Closed = 'Closed';

    public function label(): string
    {
        return match ($this) {
            self::ComReview => 'En attente — Service Com',
            self::ManagerReview => 'En attente — Manager',
            self::RHReview => 'En attente — RH',
            self::Closed => 'Clôturé',
        };
    }
}
