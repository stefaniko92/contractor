<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'title',
        'unit',
        'quantity',
        'unit_price',
        'amount',
        'currency',
        'description',
        'type',
        'discount_value',
        'discount_type',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    protected static function booted(): void
    {
        static::saved(function (InvoiceItem $invoiceItem): void {
            static::synchronizeInvoiceAmount($invoiceItem->invoice_id);

            if ($invoiceItem->wasChanged('invoice_id')) {
                static::synchronizeInvoiceAmount($invoiceItem->getOriginal('invoice_id'));
            }
        });

        static::deleted(function (InvoiceItem $invoiceItem): void {
            static::synchronizeInvoiceAmount($invoiceItem->invoice_id);
        });
    }

    private static function synchronizeInvoiceAmount(?int $invoiceId): void
    {
        if ($invoiceId) {
            Invoice::find($invoiceId)?->updateAmount();
        }
    }
}
