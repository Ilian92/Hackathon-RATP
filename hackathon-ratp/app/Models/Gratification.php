<?php

namespace App\Models;

use Database\Factories\GratificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'amount', 'reason', 'awarded_at'])]
class Gratification extends Model
{
    /** @use HasFactory<GratificationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'awarded_at' => 'date',
            'amount' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
