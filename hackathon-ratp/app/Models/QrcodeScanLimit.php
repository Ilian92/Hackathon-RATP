<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ip_address', 'count', 'window_start'])]
class QrcodeScanLimit extends Model
{
    protected function casts(): array
    {
        return [
            'window_start' => 'datetime',
        ];
    }
}
