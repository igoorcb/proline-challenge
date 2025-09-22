<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    protected $fillable = [
        'batch_id',
        'type',
        'store_id',
        'user_id',
        'latitude',
        'longitude',
        'battery_level',
        'media_url',
        'media_type',
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
