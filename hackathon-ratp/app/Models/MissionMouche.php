<?php

namespace App\Models;

use App\Enums\MissionMoucheDecision;
use App\Enums\MissionMoucheStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['driver_user_id', 'manager_user_id', 'status', 'decision', 'manager_notes', 'decided_at'])]
class MissionMouche extends Model
{
    protected function casts(): array
    {
        return [
            'status' => MissionMoucheStatus::class,
            'decision' => MissionMoucheDecision::class,
            'decided_at' => 'datetime',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function mouches(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mission_mouche_user')
            ->withPivot('submitted_at');
    }

    public function rapports(): HasMany
    {
        return $this->hasMany(RapportMouche::class);
    }

    public function submittedCount(): int
    {
        return $this->mouches()->wherePivotNotNull('submitted_at')->count();
    }

    public function isComplete(): bool
    {
        $total = $this->mouches()->count();

        return $total > 0 && $this->submittedCount() === $total;
    }
}
