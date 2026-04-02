<?php

namespace App\Enums;

enum UserRole: string
{
    case Com = 'Com';
    case Chauffeur = 'Chauffeur';
    case Manager = 'Manager';
    case RH = 'RH';
    case Avocat = 'Avocat';
    case Mouche = 'Mouche';
}
