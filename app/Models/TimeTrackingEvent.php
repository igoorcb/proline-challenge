<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeTrackingEvent extends Model
{
    protected $fillable = [
        'batch_id',
        'type',
        'latitude',
        'longitude',
        'battery_level',
        'event_created_at',
    ];

    protected $casts = [
        'event_created_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
