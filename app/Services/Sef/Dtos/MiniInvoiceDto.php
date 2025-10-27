<?php

namespace App\Services\Sef\Dtos;

class MiniInvoiceDto
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $invoiceNumber,
        public readonly ?string $issueDate,
        public readonly ?string $dueDate,
        public readonly ?float $totalAmount,
        public readonly ?string $currency,
        public readonly ?string $status,
        public readonly ?int $buyerId,
        public readonly ?string $buyerName,
        public readonly ?string $buyerTaxIdentifier,
        public readonly ?string $sefInvoiceId,
        public readonly ?string $sefInvoiceType,
        public readonly ?string $sefStatus,
        public readonly ?string $sefStatusDate,
        public readonly ?string $sefErrorMessage,
        public readonly ?bool $sendToCir,
        public readonly ?string $requestId,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['Id'] ?? null,
            invoiceNumber: $data['InvoiceNumber'] ?? null,
            issueDate: $data['IssueDate'] ?? null,
            dueDate: $data['DueDate'] ?? null,
            totalAmount: $data['TotalAmount'] ?? null,
            currency: $data['Currency'] ?? null,
            status: $data['Status'] ?? null,
            buyerId: $data['BuyerId'] ?? null,
            buyerName: $data['BuyerName'] ?? null,
            buyerTaxIdentifier: $data['BuyerTaxIdentifier'] ?? null,
            sefInvoiceId: $data['SefInvoiceId'] ?? null,
            sefInvoiceType: $data['SefInvoiceType'] ?? null,
            sefStatus: $data['SefStatus'] ?? null,
            sefStatusDate: $data['SefStatusDate'] ?? null,
            sefErrorMessage: $data['SefErrorMessage'] ?? null,
            sendToCir: $data['SendToCir'] ?? null,
            requestId: $data['RequestId'] ?? null,
            createdAt: $data['CreatedAt'] ?? null,
            updatedAt: $data['UpdatedAt'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoiceNumber,
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'status' => $this->status,
            'buyer_id' => $this->buyerId,
            'buyer_name' => $this->buyerName,
            'buyer_tax_identifier' => $this->buyerTaxIdentifier,
            'sef_invoice_id' => $this->sefInvoiceId,
            'sef_invoice_type' => $this->sefInvoiceType,
            'sef_status' => $this->sefStatus,
            'sef_status_date' => $this->sefStatusDate,
            'sef_error_message' => $this->sefErrorMessage,
            'send_to_cir' => $this->sendToCir,
            'request_id' => $this->requestId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    /**
     * Check if the invoice has SEF errors.
     */
    public function hasSefError(): bool
    {
        return ! empty($this->sefErrorMessage);
    }

    /**
     * Check if the invoice is sent to SEF.
     */
    public function isSentToSef(): bool
    {
        return ! empty($this->sefInvoiceId);
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAmount(): string
    {
        if ($this->totalAmount === null) {
            return '0.00';
        }

        return number_format($this->totalAmount, 2, ',', '.').' '.($this->currency ?? 'RSD');
    }

    /**
     * Get SEF status with error information.
     */
    public function getSefStatusWithInfo(): string
    {
        $status = $this->sefStatus ?? 'Nije poslat';

        if ($this->hasSefError()) {
            $status .= ' (Greška: '.$this->sefErrorMessage.')';
        }

        return $status;
    }
}
