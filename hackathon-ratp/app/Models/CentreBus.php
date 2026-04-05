<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'address'])]
class CentreBus extends Model
{
    use HasFactory;

    public function lignes(): HasMany
    {
        return $this->hasMany(Ligne::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('users.role', UserRole::Manager->value);
    }

    public function coms(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('users.role', UserRole::Com->value);
    }

    public function rhs(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('users.role', UserRole::RH->value);
    }

    public function avocats(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('users.role', UserRole::Avocat->value);
    }
}
