<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['matricule', 'first_name', 'last_name', 'email', 'password', 'role', 'contract_start_date', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'contract_start_date' => 'date',
        ];
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function satisfactions(): HasMany
    {
        return $this->hasMany(Satisfaction::class);
    }

    public function plannings(): HasMany
    {
        return $this->hasMany(Planning::class);
    }

    public function gratifications(): HasMany
    {
        return $this->hasMany(Gratification::class);
    }

    public function sanctions(): HasMany
    {
        return $this->hasMany(Sanction::class);
    }

    public function centreBuses(): BelongsToMany
    {
        return $this->belongsToMany(CentreBus::class);
    }

    public function chauffeurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manager_chauffeur', 'manager_id', 'chauffeur_id');
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manager_chauffeur', 'chauffeur_id', 'manager_id');
    }

    public function missionsAsChauffeur(): HasMany
    {
        return $this->hasMany(MissionMouche::class, 'driver_user_id');
    }

    public function missionsAsManager(): HasMany
    {
        return $this->hasMany(MissionMouche::class, 'manager_user_id');
    }

    public function missionsAsMouche(): BelongsToMany
    {
        return $this->belongsToMany(MissionMouche::class, 'mission_mouche_user')
            ->withPivot('submitted_at');
    }

    public function rapportsMouche(): HasMany
    {
        return $this->hasMany(RapportMouche::class, 'mouche_user_id');
    }
}
