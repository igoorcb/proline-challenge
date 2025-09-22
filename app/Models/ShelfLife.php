<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelfLife extends Model
{
    protected $fillable = [
        'batch_id',
        'type',
        'store_id',
        'product_id',
        'user_id',
        'latitude',
        'longitude',
        'battery_level',
        'expiry_date',
        'batch_code',
        'event_created_at',
    ];

    protected $casts = [
        'event_created_at' => 'datetime',
        'expiry_date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
