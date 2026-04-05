<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['email'])]
class Client extends Model
{
    use HasFactory;

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function satisfactions(): HasMany
    {
        return $this->hasMany(Satisfaction::class);
    }
}
