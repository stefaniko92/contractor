<?php

namespace App\Services\Sef\Dtos;

class ValueAddedTaxExemptionReasonDto
{
    public function __construct(
        public readonly ?string $id,
        public readonly ?string $code,
        public readonly ?string $description,
        public readonly ?string $shortDescription,
        public readonly ?bool $isActive,
        public readonly ?string $validFrom,
        public readonly ?string $validTo,
        public readonly ?string $legalBasis,
        public readonly ?int $sortOrder,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['Id'] ?? null,
            code: $data['Code'] ?? null,
            description: $data['Description'] ?? null,
            shortDescription: $data['ShortDescription'] ?? null,
            isActive: $data['IsActive'] ?? null,
            validFrom: $data['ValidFrom'] ?? null,
            validTo: $data['ValidTo'] ?? null,
            legalBasis: $data['LegalBasis'] ?? null,
            sortOrder: $data['SortOrder'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'description' => $this->description,
            'short_description' => $this->shortDescription,
            'is_active' => $this->isActive,
            'valid_from' => $this->validFrom,
            'valid_to' => $this->validTo,
            'legal_basis' => $this->legalBasis,
            'sort_order' => $this->sortOrder,
        ];
    }

    /**
     * Check if the exemption reason is currently valid.
     */
    public function isValid(): bool
    {
        if (! $this->isActive) {
            return false;
        }

        $now = new \DateTime;

        if ($this->validFrom) {
            try {
                $validFrom = new \DateTime($this->validFrom);
                if ($now < $validFrom) {
                    return false;
                }
            } catch (\Exception $e) {
                // Invalid date format, assume invalid
                return false;
            }
        }

        if ($this->validTo) {
            try {
                $validTo = new \DateTime($this->validTo);
                if ($now > $validTo) {
                    return false;
                }
            } catch (\Exception $e) {
                // Invalid date format, assume invalid
                return false;
            }
        }

        return true;
    }

    /**
     * Get the display text (prefer short description, fallback to description).
     */
    public function getDisplayText(): string
    {
        return $this->shortDescription ?? $this->description ?? $this->code ?? 'Nepoznat razlog';
    }

    /**
     * Get the full description with legal basis.
     */
    public function getFullDescription(): string
    {
        $description = $this->getDisplayText();

        if ($this->legalBasis) {
            $description .= ' (Osnov: '.$this->legalBasis.')';
        }

        return $description;
    }

    /**
     * Get formatted validity period.
     */
    public function getValidityPeriod(): string
    {
        if (! $this->validFrom && ! $this->validTo) {
            return 'Nepoznato period važenja';
        }

        $period = '';

        if ($this->validFrom) {
            try {
                $validFrom = new \DateTime($this->validFrom);
                $period .= 'Od: '.$validFrom->format('d.m.Y');
            } catch (\Exception $e) {
                $period .= 'Od: '.$this->validFrom;
            }
        }

        if ($this->validTo) {
            try {
                $validTo = new \DateTime($this->validTo);
                $period .= ($period ? ' ' : '').'Do: '.$validTo->format('d.m.Y');
            } catch (\Exception $e) {
                $period .= ($period ? ' ' : '').'Do: '.$this->validTo;
            }
        }

        return $period;
    }

    /**
     * Check if this is the default "no VAT" exemption reason.
     */
    public function isDefaultNoVatReason(): bool
    {
        return in_array($this->code, ['110', '120'], true);
    }
}
