<?php

namespace App\Services;

class IpsQrCodeService
{
    /**
     * Generate NBS IPS QR code data string
     *
     * @throws \Exception
     */
    public function generateQrCodeData(array $data): string
    {
        $requiredFields = ['recipient_account', 'recipient_name', 'amount'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        // NBS IPS QR code format
        $lines = [];

        // K: Company code (fixed)
        $lines[] = 'K:PR';

        // V: Version (01 for NBS IPS)
        $lines[] = 'V:01';

        // C: Character set (1 = UTF-8)
        $lines[] = 'C:1';

        // R: Recipient account number
        $lines[] = 'R:'.$this->formatAccountNumber($data['recipient_account']);

        // N: Recipient name
        $lines[] = 'N:'.mb_substr($data['recipient_name'], 0, 70);

        // I: Amount in dinars (RSD)
        $lines[] = 'I:'.number_format($data['amount'], 2, ',', '');

        // SF: Payment code (default 289 if not provided)
        $paymentCode = $data['payment_code'] ?? '289';
        $lines[] = 'SF:'.$paymentCode;

        // S: Purpose of payment (optional)
        if (! empty($data['purpose'])) {
            $lines[] = 'S:'.mb_substr($data['purpose'], 0, 35);
        }

        // RO: Model and reference number (optional)
        if (! empty($data['model']) && ! empty($data['reference_number'])) {
            $lines[] = 'RO:'.$data['model'].'-'.$data['reference_number'];
        }

        // O: Payer name (optional)
        if (! empty($data['payer_name'])) {
            $lines[] = 'O:'.mb_substr($data['payer_name'], 0, 70);
        }

        return implode("\n", $lines);
    }

    /**
     * Format account number for NBS IPS QR code
     */
    private function formatAccountNumber(string $account): string
    {
        // Remove any spaces and dashes
        $account = preg_replace('/[\s\-]/', '', $account);

        // Ensure it's 18 digits
        if (strlen($account) !== 18) {
            // Try to format if it's missing dashes
            if (strlen($account) < 18) {
                // Pad with zeros if needed
                $account = str_pad($account, 18, '0', STR_PAD_LEFT);
            }
        }

        return $account;
    }
}
