<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\CentreBusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'address'])]
class CentreBus extends Model
{
    /** @use HasFactory<CentreBusFactory> */
    use HasFactory;

    /** @return HasMany<Ligne, $this> */
    public function lignes(): HasMany
    {
        return $this->hasMany(Ligne::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('users.role', UserRole::Manager->value);
    }

    /** @return BelongsToMany<User, $this> */
    public function coms(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('users.role', UserRole::Com->value);
    }

    /** @return BelongsToMany<User, $this> */
    public function rhs(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('users.role', UserRole::RH->value);
    }

    /** @return BelongsToMany<User, $this> */
    public function avocats(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('users.role', UserRole::Avocat->value);
    }
}
