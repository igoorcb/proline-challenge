<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    protected $fillable = [
        'batch_id',
        'type',
        'operation_name',
        'latitude',
        'longitude',
        'battery_level',
        'image',
        'status',
        'file_path',
        'file_size',
        'media_created_at',
    ];

    protected $casts = [
        'media_created_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'file_size' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
