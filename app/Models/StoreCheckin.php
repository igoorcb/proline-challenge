<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCheckin extends Model
{
    protected $fillable = [
        'batch_id',
        'store_id',
        'user_id',
        'latitude',
        'longitude',
        'battery_level',
        'checkin_at',
    ];

    protected $casts = [
        'checkin_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
