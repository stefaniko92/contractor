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

        $parts = [];

        // K: NBS IPS nalog za plaćanje
        $parts[] = 'K:PR';

        // V: Verzija
        $parts[] = 'V:01';

        // C: Karakter set (1 = UTF-8)
        $parts[] = 'C:1';

        // R: Račun primaoca – TAČNO 18 cifara, bez crtice
        $parts[] = 'R:' . $this->formatAccountNumber($data['recipient_account']);

        // N: Naziv primaoca (max 70)
        $parts[] = 'N:' . $this->sanitizeText($data['recipient_name'], 70);

        // I: Iznos – RSD####,##
        $parts[] = 'I:' . $this->formatAmount($data['amount']);

        // SF: Šifra plaćanja (npr. 289 ili 253)
        $paymentCode = $data['payment_code'] ?? '289';
        $parts[] = 'SF:' . $paymentCode;

        // S: Svrha plaćanja (opciono, max 35)
        if (!empty($data['purpose'])) {
            $parts[] = 'S:' . $this->sanitizeText($data['purpose'], 35);
        }

        // RO: Model + poziv na broj (opciono)
        if (!empty($data['model']) && !empty($data['reference_number'])) {
            $model = $this->formatModel($data['model']);              // npr. "97"
            $ref   = $this->formatReferenceNumber($data['reference_number']); // samo cifre
            $parts[] = 'RO:' . $model . $ref;                         // npr. "971234567890..."
        }

        // P: Platilac (opciono; ako ga nema, tag se uopšte NE dodaje)
        if (!empty($data['payer_name'])) {
            // Ako hoćeš i adresu: spoji ime + adresu sa "\r\n"
            $payer = $this->sanitizeText($data['payer_name'], 70);
            $parts[] = 'P:' . $payer;
        }

        // Važno: string se NE sme završavati sa '|'
        return implode('|', $parts);
    }

    /**
     * Račun mora imati tačno 18 cifara (bez '-', ' ').
     * Ovde sam namerno stroži – ako nije 18 cifara, baca exception,
     * da ne bi "mažio" loš račun nulama na pogrešnom mestu.
     */
    private function formatAccountNumber(string $account): string
    {
        // ukloni razmake i crtice
        $account = preg_replace('/[\s-]/', '', $account);

        // mora da sadrži samo cifre
        if (!ctype_digit($account)) {
            throw new \Exception("Invalid account number: must contain only digits");
        }

        // minimum je 4 cifre (3 za banku + bar 1 za račun),
        // više od 18 nije dozvoljeno
        $length = strlen($account);

        if ($length < 4) {
            throw new \Exception("Invalid account number: too short to contain bank code + account");
        }

        if ($length > 18) {
            throw new \Exception("Invalid account number: longer than 18 digits");
        }

        // tačno 18 cifara → već je OK
        if ($length === 18) {
            return $account;
        }

        // manje od 18 → dopunjuj deo posle prve 3 cifre nulama s LEVE strane
        $bankCode = substr($account, 0, 3);
        $rest     = substr($account, 3);

        if (strlen($rest) > 15) {
            // teoretski ne bi trebalo da se desi, ali čisto radi sigurnosti
            throw new \Exception("Invalid account number: account part longer than 15 digits");
        }

        // dopuni deo računa na 15 cifara nulama S LEVE STRANE
        $restPadded = str_pad($rest, 15, '0', STR_PAD_LEFT);

        return $bankCode . $restPadded;
    }

    /**
     * I: RSD + iznos sa zarezom i dve decimale
     * Primer: 15885.64 -> "RSD15885,64"
     */
    private function formatAmount(float $amount): string
    {
        return 'RSD' . number_format($amount, 2, ',', '');
    }

    /**
     * Sanira tekst za tagove N, S, P – skida '|' i seče na max dužinu.
     */
    private function sanitizeText(string $text, int $limit): string
    {
        $text = trim($text);
        $text = str_replace('|', ' ', $text); // '|' ne sme ući u payload
        return mb_substr($text, 0, $limit, 'UTF-8');
    }

    /**
     * Model – dve cifre (00, 97...)
     */
    private function formatModel(string $model): string
    {
        $model = preg_replace('/\D/', '', $model);
        return str_pad($model, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Poziv na broj – samo cifre (bez '-', '/', ' ')
     */
    private function formatReferenceNumber(string $reference): string
    {
        return preg_replace('/\D/', '', $reference);
    }
}
