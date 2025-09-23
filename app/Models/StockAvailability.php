<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAvailability extends Model
{
    protected $fillable = [
        'batch_id',
        'type',
        'operation_id',
        'store_id',
        'user_id',
        'product_id',
        'product_name',
        'quantity_available',
        'quantity_required',
        'stock_status',
        'location',
        'started_at',
        'registered_at',
        'finished_at',
    ];

    protected $casts = [
        'quantity_available' => 'integer',
        'quantity_required' => 'integer',
        'started_at' => 'datetime',
        'registered_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
