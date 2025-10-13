<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'tax_id',
        'address',
        'email',
        'phone',
        'notes',
        'is_domestic',
        'client_type',
        'default_place_of_sale',
        'city',
        'country',
        'vat_number',
        'registration_number',
        'currency',
    ];

    protected $casts = [
        // Removed 'is_domestic' => 'boolean' cast to work properly with integer radio options
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
