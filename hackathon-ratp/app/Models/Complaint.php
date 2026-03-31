<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['description', 'status', 'incident_time', 'bus_id', 'complaint_type_id', 'user_id', 'client_id'])]
class Complaint extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'incident_time' => 'datetime',
            'status' => ComplaintStatus::class,
        ];
    }

    /** @return BelongsTo<Bus, $this> */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /** @return BelongsTo<ComplaintType, $this> */
    public function complaintType(): BelongsTo
    {
        return $this->belongsTo(ComplaintType::class);
    }

    /** @return BelongsTo<User, $this> */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Client, $this> */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** @return HasOne<Severity, $this> */
    public function severity(): HasOne
    {
        return $this->hasOne(Severity::class);
    }
}
