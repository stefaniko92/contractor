<?php

namespace Tests\Unit;

use App\Services\IpsQrCodeService;
use PHPUnit\Framework\TestCase;

class IpsQrCodeServiceTest extends TestCase
{
    public function test_it_generates_a_valid_model_97_reference_for_invoice_numbers(): void
    {
        $service = new IpsQrCodeService;

        $qrCodeData = $service->generateQrCodeData([
            'recipient_account' => '160-44224995-00',
            'recipient_name' => 'SR SOFTWARE NIS',
            'amount' => 37500.00,
            'payment_code' => '289',
            'model' => '97',
            'reference_number' => '14/2026',
            'payer_name' => 'SWIFTY LABS DOO VRANJE',
        ]);

        $this->assertStringContainsString('RO:9744142026', $qrCodeData);
    }

    public function test_it_keeps_an_already_valid_model_97_reference_unchanged(): void
    {
        $service = new IpsQrCodeService;

        $qrCodeData = $service->generateQrCodeData([
            'recipient_account' => '160-44224995-00',
            'recipient_name' => 'SR SOFTWARE NIS',
            'amount' => 37500.00,
            'payment_code' => '289',
            'model' => '97',
            'reference_number' => '44142026',
            'payer_name' => 'SWIFTY LABS DOO VRANJE',
        ]);

        $this->assertStringContainsString('RO:9744142026', $qrCodeData);
    }
}
