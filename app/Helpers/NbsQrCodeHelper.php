<?php

namespace App\Helpers;

use App\Services\IpsQrCodeService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class NbsQrCodeHelper
{
    /**
     * Generate NBS IPS QR code as a Base64-encoded SVG.
     *
     * @throws \Exception
     */
    public static function generateQrCodeBase64(array $data, int $size = 300): string
    {
        try {
            $service = new IpsQrCodeService;
            $qrCodeData = $service->generateQrCodeData($data);

            // Generate QR code as Base64-encoded SVG
            $svgData = QrCode::format('svg')->size($size)->encoding('UTF-8')->generate($qrCodeData);

            return 'data:image/svg+xml;base64,'.base64_encode($svgData);
        } catch (\Exception $e) {
            \Log::warning('QR code generation error (SVG): '.$e->getMessage());
            throw $e;
        }
    }
}
