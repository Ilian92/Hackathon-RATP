<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['first_name', 'last_name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /** @return HasMany<Complaint, $this> */
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /** @return HasMany<Satisfaction, $this> */
    public function satisfactions(): HasMany
    {
        return $this->hasMany(Satisfaction::class);
    }

    /** @return HasMany<Planning, $this> */
    public function plannings(): HasMany
    {
        return $this->hasMany(Planning::class);
    }

    /** @return BelongsToMany<CentreBus, $this> */
    public function centreBuses(): BelongsToMany
    {
        return $this->belongsToMany(CentreBus::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function chauffeurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manager_chauffeur', 'manager_id', 'chauffeur_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manager_chauffeur', 'chauffeur_id', 'manager_id');
    }
}
