<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'invoice_number',
        'amount',
        'description',
        'currency',
        'issue_date',
        'due_date',
        'trading_place',
        'status',
        'pdf_path',
        'invoice_type',
        'invoice_document_type',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public static function generateInvoiceNumber(int $userId, ?int $year = null): string
    {
        $year = $year ?? now()->year;

        $count = static::where('user_id', $userId)
            ->whereYear('issue_date', $year)
            ->count();

        return ($count + 1).'/'.$year;
    }

    public function updateAmount(): void
    {
        $totalAmount = $this->items()->sum('amount');
        $this->updateQuietly(['amount' => $totalAmount]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber(
                    $invoice->user_id,
                    $invoice->issue_date ? $invoice->issue_date->year : now()->year
                );
            }
        });

        static::saved(function ($invoice) {
            $invoice->updateAmount();
        });

    }
}
