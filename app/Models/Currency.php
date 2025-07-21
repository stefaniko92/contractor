<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate_to_rsd',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'exchange_rate_to_rsd' => 'decimal:4',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
