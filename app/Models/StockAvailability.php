<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAvailability extends Model
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
        'available',
        'quantity',
        'event_created_at',
    ];

    protected $casts = [
        'event_created_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'available' => 'boolean',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
