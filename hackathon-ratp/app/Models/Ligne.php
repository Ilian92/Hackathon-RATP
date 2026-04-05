<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nom', 'centre_bus_id'])]
class Ligne extends Model
{
    use HasFactory;

    public function centreBus(): BelongsTo
    {
        return $this->belongsTo(CentreBus::class);
    }

    public function arrets(): BelongsToMany
    {
        return $this->belongsToMany(Arret::class)->withPivot('ordre')->orderByPivot('ordre');
    }

    public function premierArret(): ?Arret
    {
        return $this->arrets()->orderByPivot('ordre')->first();
    }

    public function dernierArret(): ?Arret
    {
        return $this->arrets()->orderByPivot('ordre', 'desc')->first();
    }
}
