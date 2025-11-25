<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpoEntry extends Model
{
    protected $fillable = [
        'kpo_upload_id',
        'user_id',
        'client_id',
        'invoice_id',
        'entry_number',
        'date',
        'invoice_mark',
        'product_service_description',
        'client_name',
        'income_amount',
        'expense_amount',
        'currency',
        'raw_data',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'income_amount' => 'decimal:2',
            'expense_amount' => 'decimal:2',
            'raw_data' => 'array',
        ];
    }

    public function kpoUpload(): BelongsTo
    {
        return $this->belongsTo(KpoUpload::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function isIncome(): bool
    {
        return $this->income_amount > 0;
    }

    public function isExpense(): bool
    {
        return $this->expense_amount > 0;
    }
}
