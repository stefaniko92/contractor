<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EfakturaInvoice extends Model
{
    protected $fillable = [
        'invoice_id',
        'user_id',
        'sef_invoice_id',
        'sef_invoice_number',
        'sef_request_id',
        'status',
        'sent_at',
        'delivered_at',
        'accepted_at',
        'rejected_at',
        'cancelled_at',
        'sef_response',
        'status_history',
        'last_webhook_at',
        'last_webhook_payload',
        'last_error',
        'last_error_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_webhook_at' => 'datetime',
        'last_error_at' => 'datetime',
        'sef_response' => 'array',
        'status_history' => 'array',
        'last_webhook_payload' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update the status and add to history
     */
    public function updateStatus(string $newStatus, ?array $additionalData = null): void
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;

        // Update timestamp based on status
        $timestampField = match ($newStatus) {
            'sent' => 'sent_at',
            'delivered' => 'delivered_at',
            'accepted' => 'accepted_at',
            'rejected' => 'rejected_at',
            'cancelled' => 'cancelled_at',
            default => null,
        };

        if ($timestampField && ! $this->{$timestampField}) {
            $this->{$timestampField} = now();
        }

        // Add to status history
        $history = $this->status_history ?? [];
        $history[] = [
            'from' => $oldStatus,
            'to' => $newStatus,
            'changed_at' => now()->toISOString(),
            'data' => $additionalData,
        ];
        $this->status_history = $history;

        $this->save();
    }

    /**
     * Refresh invoice status from eFaktura API
     */
    public function refreshStatus(): array
    {
        if (! $this->sef_invoice_id) {
            return [
                'error' => 'No eFaktura invoice ID found',
            ];
        }

        $sefService = \App\Services\SefService::forUser($this->user_id);
        $response = $sefService->getInvoiceStatus($this->sef_invoice_id);

        if (isset($response['error'])) {
            $this->update([
                'last_error' => $response['error'],
                'last_error_at' => now(),
            ]);

            return $response;
        }

        // Update status based on response (API returns "Status" with capital S)
        $newStatus = $response['Status'] ?? $response['status'] ?? null;

        if ($newStatus) {
            // Map eFaktura statuses to our internal statuses
            $mappedStatus = match (strtolower($newStatus)) {
                'sent' => 'sent',
                'delivered' => 'delivered',
                'accepted' => 'accepted',
                'rejected' => 'rejected',
                'cancelled' => 'cancelled',
                default => strtolower($newStatus),
            };

            $this->updateStatus($mappedStatus, $response);
        }

        return $response;
    }
}
