<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'mission_mouche_id', 'mouche_user_id', 'ligne_id', 'date_observation',
    'ponctualite', 'conduite', 'politesse', 'tenue', 'securite', 'gestion_conflit', 'observation',
])]
class RapportMouche extends Model
{
    protected function casts(): array
    {
        return [
            'date_observation' => 'date',
            'ponctualite' => 'integer',
            'conduite' => 'integer',
            'politesse' => 'integer',
            'tenue' => 'integer',
            'securite' => 'integer',
            'gestion_conflit' => 'integer',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(MissionMouche::class, 'mission_mouche_id');
    }

    public function mouche(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mouche_user_id');
    }

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(Ligne::class);
    }

    public function averageScore(): float
    {
        $scores = array_filter([
            $this->ponctualite,
            $this->conduite,
            $this->politesse,
            $this->tenue,
            $this->securite,
            $this->gestion_conflit,
        ]);

        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
    }
}
