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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /** @return BelongsTo<Bus, $this> */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /** @return BelongsTo<User, $this> */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Ligne, $this> */
    public function ligne(): BelongsTo
    {
        return $this->belongsTo(Ligne::class);
    }

    /** @return BelongsTo<Arret, $this> */
    public function arretDebut(): BelongsTo
    {
        return $this->belongsTo(Arret::class, 'arret_debut_id');
    }

    /** @return BelongsTo<Arret, $this> */
    public function arretFin(): BelongsTo
    {
        return $this->belongsTo(Arret::class, 'arret_fin_id');
    }
}
