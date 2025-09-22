<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceSurvey extends Model
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
        'price',
        'event_created_at',
    ];

    protected $casts = [
        'event_created_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'price' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
