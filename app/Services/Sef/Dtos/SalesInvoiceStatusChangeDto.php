<?php

namespace App\Services\Sef\Dtos;

class SalesInvoiceStatusChangeDto
{
    public function __construct(
        public readonly ?int $invoiceId,
        public readonly ?string $invoiceNumber,
        public readonly ?string $previousStatus,
        public readonly ?string $currentStatus,
        public readonly ?string $statusChangeDate,
        public readonly ?string $sefInvoiceId,
        public readonly ?string $sefStatusChangeType,
        public readonly ?string $sefStatusChangeReason,
        public readonly ?string $sefOperatorName,
        public readonly ?string $sefOperatorCode,
        public readonly ?string $errorMessage,
        public readonly ?string $buyerName,
        public readonly ?string $buyerTaxIdentifier,
        public readonly ?float $totalAmount,
        public readonly ?string $currency,
        public readonly ?string $issueDate,
        public readonly ?string $dueDate,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            invoiceId: $data['InvoiceId'] ?? null,
            invoiceNumber: $data['InvoiceNumber'] ?? null,
            previousStatus: $data['PreviousStatus'] ?? null,
            currentStatus: $data['CurrentStatus'] ?? null,
            statusChangeDate: $data['StatusChangeDate'] ?? null,
            sefInvoiceId: $data['SefInvoiceId'] ?? null,
            sefStatusChangeType: $data['SefStatusChangeType'] ?? null,
            sefStatusChangeReason: $data['SefStatusChangeReason'] ?? null,
            sefOperatorName: $data['SefOperatorName'] ?? null,
            sefOperatorCode: $data['SefOperatorCode'] ?? null,
            errorMessage: $data['ErrorMessage'] ?? null,
            buyerName: $data['BuyerName'] ?? null,
            buyerTaxIdentifier: $data['BuyerTaxIdentifier'] ?? null,
            totalAmount: $data['TotalAmount'] ?? null,
            currency: $data['Currency'] ?? null,
            issueDate: $data['IssueDate'] ?? null,
            dueDate: $data['DueDate'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'invoice_id' => $this->invoiceId,
            'invoice_number' => $this->invoiceNumber,
            'previous_status' => $this->previousStatus,
            'current_status' => $this->currentStatus,
            'status_change_date' => $this->statusChangeDate,
            'sef_invoice_id' => $this->sefInvoiceId,
            'sef_status_change_type' => $this->sefStatusChangeType,
            'sef_status_change_reason' => $this->sefStatusChangeReason,
            'sef_operator_name' => $this->sefOperatorName,
            'sef_operator_code' => $this->sefOperatorCode,
            'error_message' => $this->errorMessage,
            'buyer_name' => $this->buyerName,
            'buyer_tax_identifier' => $this->buyerTaxIdentifier,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
        ];
    }

    /**
     * Check if this is a SEF status change.
     */
    public function isSefStatusChange(): bool
    {
        return ! empty($this->sefInvoiceId) && ! empty($this->sefStatusChangeType);
    }

    /**
     * Check if there's an error in the status change.
     */
    public function hasError(): bool
    {
        return ! empty($this->errorMessage);
    }

    /**
     * Get the status change type description in Serbian.
     */
    public function getStatusChangeTypeDescription(): string
    {
        return match ($this->sefStatusChangeType) {
            'InvoiceSent' => 'Faktura poslata',
            'InvoiceReceived' => 'Faktura primljena',
            'InvoiceAccepted' => 'Faktura prihvaćena',
            'InvoiceRejected' => 'Faktura odbijena',
            'InvoiceCanceled' => 'Faktura otkazana',
            'InvoiceStorno' => 'Faktura stornirana',
            'InvoiceCorrected' => 'Faktura korigovana',
            default => $this->sefStatusChangeType ?? 'Nepoznat tip promene',
        };
    }

    /**
     * Get readable status change description.
     */
    public function getStatusChangeDescription(): string
    {
        $description = '';

        if ($this->previousStatus && $this->currentStatus) {
            $description = "Status promenjen iz '{$this->previousStatus}' u '{$this->currentStatus}'";
        }

        if ($this->isSefStatusChange()) {
            $description .= ' ('.$this->getStatusChangeTypeDescription().')';
        }

        if ($this->hasError()) {
            $description .= ' - Greška: '.$this->errorMessage;
        }

        return $description;
    }

    /**
     * Get formatted date for status change.
     */
    public function getFormattedStatusChangeDate(): string
    {
        if (! $this->statusChangeDate) {
            return '';
        }

        try {
            $date = new \DateTime($this->statusChangeDate);

            return $date->format('d.m.Y H:i:s');
        } catch (\Exception $e) {
            return $this->statusChangeDate;
        }
    }
}
