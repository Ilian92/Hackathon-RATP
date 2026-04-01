<?php

namespace App\Models;

use Database\Factories\LigneFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nom', 'centre_bus_id'])]
class Ligne extends Model
{
    /** @use HasFactory<LigneFactory> */
    use HasFactory;

    /** @return BelongsTo<CentreBus, $this> */
    public function centreBus(): BelongsTo
    {
        return $this->belongsTo(CentreBus::class);
    }

    /** @return BelongsToMany<Arret, $this> */
    public function arrets(): BelongsToMany
    {
        return $this->belongsToMany(Arret::class)->withPivot('ordre')->orderByPivot('ordre');
    }

    /** @return BelongsTo<Arret, $this> */
    public function premierArret(): BelongsTo
    {
        return $this->belongsTo(Arret::class, 'id', 'id')
            ->join('arret_ligne', 'arrets.id', '=', 'arret_ligne.arret_id')
            ->where('arret_ligne.ligne_id', $this->id)
            ->orderBy('arret_ligne.ordre')
            ->limit(1);
    }

    /** @return BelongsTo<Arret, $this> */
    public function dernierArret(): BelongsTo
    {
        return $this->belongsTo(Arret::class, 'id', 'id')
            ->join('arret_ligne', 'arrets.id', '=', 'arret_ligne.arret_id')
            ->where('arret_ligne.ligne_id', $this->id)
            ->orderByDesc('arret_ligne.ordre')
            ->limit(1);
    }
}
