<?php

namespace App\Services\Sef;

use App\Services\SefService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VatProfileResolver
{
    private SefService $sefService;

    // VAT profiles for different scenarios
    private const PROFILES = [
        'pausalni' => [
            'category_id' => 'O', // Outside scope of VAT
            'percent' => 0,
            'needs_exemption_reason' => true,
            'default_exemption_code' => 'PDV-RS-PAUSALNI',
            'description' => 'Paušalno oporezivanje - nije obveznik PDV',
        ],
        'standard_20' => [
            'category_id' => 'S',
            'percent' => 20,
            'needs_exemption_reason' => false,
            'description' => 'Standardna stopa PDV 20%',
        ],
        'standard_10' => [
            'category_id' => 'S',
            'percent' => 10,
            'needs_exemption_reason' => false,
            'description' => 'Snižena stopa PDV 10%',
        ],
        'zero_rated' => [
            'category_id' => 'Z',
            'percent' => 0,
            'needs_exemption_reason' => false,
            'description' => 'Oslobođeno sa pravom na odbitak',
        ],
        'exempt' => [
            'category_id' => 'E',
            'percent' => 0,
            'needs_exemption_reason' => true,
            'default_exemption_code' => 'PDV-RS-EXEMPT',
            'description' => 'Oslobođeno bez prava na odbitak',
        ],
    ];

    public function __construct(SefService $sefService)
    {
        $this->sefService = $sefService;
    }

    /**
     * Resolve VAT profile based on user type and configuration
     */
    public function resolveForInvoice(array $context): VatProfile
    {
        // For paušalci (flat-tax entrepreneurs)
        if ($context['is_pausalni'] ?? false) {
            return $this->getProfile('pausalni');
        }

        // For regular VAT payers
        if ($context['vat_rate'] ?? 0) {
            $rate = (float) $context['vat_rate'];
            if ($rate === 20.0) {
                return $this->getProfile('standard_20');
            } elseif ($rate === 10.0) {
                return $this->getProfile('standard_10');
            } elseif ($rate === 0.0) {
                return $this->getProfile('zero_rated');
            }
        }

        // Default to pausalni for backward compatibility
        return $this->getProfile('pausalni');
    }

    /**
     * Get a specific VAT profile
     */
    public function getProfile(string $key): VatProfile
    {
        $profile = self::PROFILES[$key] ?? self::PROFILES['pausalni'];

        $exemptionReasons = [];
        if ($profile['needs_exemption_reason']) {
            $exemptionReasons = $this->getExemptionReasons();

            // Find the matching exemption reason
            $exemptionCode = $profile['default_exemption_code'] ?? '';
            $exemptionText = $profile['description'];

            // Try to find official SEF exemption reason
            foreach ($exemptionReasons as $reason) {
                if (str_contains($reason['code'] ?? '', 'pausal') ||
                    str_contains($reason['description'] ?? '', 'pausal')) {
                    $exemptionCode = $reason['code'];
                    $exemptionText = $reason['description'];
                    break;
                }
            }
        }

        return new VatProfile(
            categoryId: $profile['category_id'],
            percent: $profile['percent'],
            exemptionReasonCode: $profile['needs_exemption_reason'] ? $exemptionCode : null,
            exemptionReasonText: $profile['needs_exemption_reason'] ? $exemptionText : null,
            description: $profile['description']
        );
    }

    /**
     * Get valid VAT exemption reasons from SEF
     */
    public function getExemptionReasons(): array
    {
        return Cache::remember('sef_vat_exemption_reasons', 86400, function () {
            $response = $this->sefService->getValueAddedTaxExemptionReasonList();

            if (isset($response['error'])) {
                Log::error('Failed to fetch VAT exemption reasons', ['error' => $response['error']]);
                return [];
            }

            $reasons = [];
            foreach ($response['exemption_reasons'] ?? [] as $reason) {
                if (is_object($reason)) {
                    $reasons[] = [
                        'code' => property_exists($reason, 'code') ? $reason->code : '',
                        'description' => property_exists($reason, 'description') ? $reason->description : '',
                    ];
                } else {
                    $reasons[] = $reason;
                }
            }

            return $reasons;
        });
    }

    /**
     * Validate if a VAT profile is valid for SEF
     */
    public function validate(VatProfile $profile): array
    {
        $errors = [];

        // Category S must have 10% or 20% rate
        if ($profile->categoryId === 'S' && !in_array($profile->percent, [10, 20])) {
            $errors[] = 'Category S requires 10% or 20% VAT rate';
        }

        // Category O and E require exemption reason
        if (in_array($profile->categoryId, ['O', 'E']) && empty($profile->exemptionReasonCode)) {
            $errors[] = "Category {$profile->categoryId} requires exemption reason";
        }

        // Validate exemption reason exists in SEF
        if ($profile->exemptionReasonCode) {
            $validReasons = $this->getExemptionReasons();
            $found = false;
            foreach ($validReasons as $reason) {
                if ($reason['code'] === $profile->exemptionReasonCode) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                Log::warning('VAT exemption code not found in SEF list', [
                    'code' => $profile->exemptionReasonCode,
                    'available_codes' => array_column($validReasons, 'code'),
                ]);
                // Don't fail validation, just warn
            }
        }

        return $errors;
    }
}

/**
 * Data Transfer Object for VAT profile
 */
class VatProfile
{
    public function __construct(
        public readonly string $categoryId,
        public readonly float $percent,
        public readonly ?string $exemptionReasonCode,
        public readonly ?string $exemptionReasonText,
        public readonly string $description
    ) {}

    public function toArray(): array
    {
        return [
            'categoryId' => $this->categoryId,
            'percent' => $this->percent,
            'exemptionReasonCode' => $this->exemptionReasonCode,
            'exemptionReasonText' => $this->exemptionReasonText,
            'description' => $this->description,
        ];
    }
}