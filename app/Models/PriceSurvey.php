<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceSurvey extends Model
{
    protected $fillable = [
        'batch_id',
        'type',
        'survey_id',
        'store_id',
        'user_id',
        'product_id',
        'product_category',
        'price',
        'currency',
        'started_at',
        'registered_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'registered_at' => 'datetime',
        'finished_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
