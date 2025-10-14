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
        'bank_account_id',
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
        'is_storno',
        'original_invoice_id',
        'original_invoice_number',
        'original_invoice_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'original_invoice_date' => 'date',
        'amount' => 'decimal:2',
        'is_storno' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the original invoice if this is a storno invoice
     */
    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id');
    }

    /**
     * Get all storno invoices for this invoice
     */
    public function stornoInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'original_invoice_id');
    }

    public static function generateInvoiceNumber(int $userId, ?int $year = null, string $documentType = 'faktura', string $prefix = ''): string
    {
        $year = $year ?? now()->year;

        // Set default prefixes for different document types
        if (empty($prefix)) {
            $prefix = match($documentType) {
                'avansna_faktura' => 'A',
                'profaktura' => 'P',
                'faktura' => '',
                default => ''
            };
        }

        // Create the pattern to match existing numbers for this document type and prefix
        $pattern = $prefix ? '^' . preg_quote($prefix) . '([0-9]+)/' . $year . '$' : '^[0-9]+/' . $year . '$';

        // Find the highest number for this document type, prefix, year and user
        $highestNumber = static::where('user_id', $userId)
            ->whereYear('issue_date', $year)
            ->where('invoice_document_type', $documentType)
            ->where('invoice_number', 'REGEXP', $pattern)
            ->get()
            ->map(function ($invoice) use ($prefix) {
                $number = $invoice->invoice_number;
                if ($prefix) {
                    // Extract number from "A1/2025" -> 1
                    $parts = explode('/', $number);
                    return (int) substr($parts[0], strlen($prefix));
                } else {
                    // Extract number from "25/2025" -> 25
                    return (int) explode('/', $number)[0];
                }
            })
            ->max() ?? 0;

        return $prefix . ($highestNumber + 1) . '/' . $year;
    }

    public function updateAmount(): void
    {
        // Don't auto-update amount for storno invoices as they have manually set negative amounts
        if ($this->is_storno) {
            return;
        }
        
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
                    $invoice->issue_date ? $invoice->issue_date->year : now()->year,
                    $invoice->invoice_document_type ?? 'faktura'
                );
            }
        });

        static::saved(function ($invoice) {
            $invoice->updateAmount();
        });

        static::deleting(function ($invoice) {
            // If this is a storno invoice being deleted, restore the original invoice
            if ($invoice->is_storno && $invoice->original_invoice_id) {
                $originalInvoice = static::find($invoice->original_invoice_id);
                if ($originalInvoice) {
                    // Restore based on actual payment records
                    // If the original invoice has related income/payment records, it should be 'charged'
                    // Otherwise, restore to 'issued'
                    $hasPayments = $originalInvoice->incomes()->count() > 0;
                    $newStatus = $hasPayments ? 'charged' : 'issued';
                    
                    $originalInvoice->update(['status' => $newStatus]);
                }
            }
        });
    }
}
