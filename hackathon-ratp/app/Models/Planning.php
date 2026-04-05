<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['bus_id', 'user_id', 'date', 'ligne_id', 'arret_debut_id', 'heure_debut', 'arret_fin_id', 'heure_fin'])]
class Planning extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(Ligne::class);
    }

    public function arretDebut(): BelongsTo
    {
        return $this->belongsTo(Arret::class, 'arret_debut_id');
    }

    public function arretFin(): BelongsTo
    {
        return $this->belongsTo(Arret::class, 'arret_fin_id');
    }
}
