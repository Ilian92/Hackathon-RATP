<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintStep;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['description', 'negative', 'status', 'step', 'incident_time', 'bus_id', 'complaint_type_id', 'user_id', 'client_id', 'com_user_id', 'manager_user_id', 'rh_user_id'])]
class Complaint extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'incident_time' => 'datetime',
            'status' => ComplaintStatus::class,
            'step' => ComplaintStep::class,
        ];
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function complaintType(): BelongsTo
    {
        return $this->belongsTo(ComplaintType::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function severity(): HasOne
    {
        return $this->hasOne(Severity::class);
    }

    public function sanction(): HasOne
    {
        return $this->hasOne(Sanction::class);
    }

    public function gratification(): HasOne
    {
        return $this->hasOne(Gratification::class);
    }

    public function comAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'com_user_id');
    }

    public function managerAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function rhAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rh_user_id');
    }
}
