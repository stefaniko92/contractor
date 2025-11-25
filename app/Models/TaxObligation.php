<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxObligation extends Model
{
    protected $fillable = [
        'user_id', 'tax_resolution_id', 'type', 'description',
        'amount', 'currency', 'year', 'month', 'due_date', 'status', 'paid_at',
        'payment_code', 'payment_recipient_account', 'payment_payer_account',
        'payment_model', 'payment_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taxResolution(): BelongsTo
    {
        return $this->belongsTo(TaxResolution::class);
    }

    public function markAsPaid(?\DateTime $paidAt = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => $paidAt ?? now(),
        ]);
    }
}
