<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelfLife extends Model
{
    protected $fillable = [
        'batch_id',
        'type',
        'operation_id',
        'store_id',
        'user_id',
        'product_id',
        'product_name',
        'expiry_date',
        'days_to_expire',
        'condition',
        'started_at',
        'registered_at',
        'finished_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'days_to_expire' => 'integer',
        'started_at' => 'datetime',
        'registered_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
