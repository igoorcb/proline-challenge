<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'filename',
        'raw_data',
        'status',
        'total_items',
        'processed_items',
        'failed_items',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function timeTrackingEvents(): HasMany
    {
        return $this->hasMany(TimeTrackingEvent::class);
    }

    public function storeCheckins(): HasMany
    {
        return $this->hasMany(StoreCheckin::class);
    }

    public function priceSurveys(): HasMany
    {
        return $this->hasMany(PriceSurvey::class);
    }

    public function shelfLives(): HasMany
    {
        return $this->hasMany(ShelfLife::class);
    }

    public function stockAvailabilities(): HasMany
    {
        return $this->hasMany(StockAvailability::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }
}
