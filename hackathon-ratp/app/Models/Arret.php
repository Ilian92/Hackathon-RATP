<?php

namespace App\Models;

use Database\Factories\ArretFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nom', 'latitude', 'longitude'])]
class Arret extends Model
{
    /** @use HasFactory<ArretFactory> */
    use HasFactory;

    /** @return BelongsToMany<Ligne, $this> */
    public function lignes(): BelongsToMany
    {
        return $this->belongsToMany(Ligne::class)->withPivot('ordre');
    }
}
