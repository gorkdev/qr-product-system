<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVisit extends Model
{
    protected $fillable = [
        'product_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'city',
        'country',
        'region_name',
        'timezone',
        'isp',
        'lat',
        'lon',
        'visited_at',
        'error_message',
        'is_anonymous',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'is_anonymous' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
