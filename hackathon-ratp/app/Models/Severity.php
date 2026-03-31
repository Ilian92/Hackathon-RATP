<?php

namespace App\Models;

use Database\Factories\SeverityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['complaint_id', 'user_id', 'level', 'justification'])]
class Severity extends Model
{
    /** @use HasFactory<SeverityFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }

    /** @return BelongsTo<Complaint, $this> */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    /** @return BelongsTo<User, $this> */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
