<?php

namespace App\Services\Sef\Dtos;

class EfakturaVersionDto
{
    public function __construct(
        public readonly ?string $version,
        public readonly ?string $releaseDate,
        public readonly ?string $description,
        public readonly ?string $apiVersion,
        public readonly ?array $supportedFormats,
        public readonly ?array $supportedFeatures,
        public readonly ?string $deprecationNotice,
        public readonly ?string $updateRequired,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            version: $data['Version'] ?? null,
            releaseDate: $data['ReleaseDate'] ?? null,
            description: $data['Description'] ?? null,
            apiVersion: $data['ApiVersion'] ?? null,
            supportedFormats: $data['SupportedFormats'] ?? [],
            supportedFeatures: $data['SupportedFeatures'] ?? [],
            deprecationNotice: $data['DeprecationNotice'] ?? null,
            updateRequired: $data['UpdateRequired'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'release_date' => $this->releaseDate,
            'description' => $this->description,
            'api_version' => $this->apiVersion,
            'supported_formats' => $this->supportedFormats,
            'supported_features' => $this->supportedFeatures,
            'deprecation_notice' => $this->deprecationNotice,
            'update_required' => $this->updateRequired,
        ];
    }

    /**
     * Get the full version string.
     */
    public function getFullVersionString(): string
    {
        $version = $this->version ?? 'Nepoznata verzija';

        if ($this->apiVersion) {
            $version .= ' (API: '.$this->apiVersion.')';
        }

        return $version;
    }

    /**
     * Check if the version supports UBL format.
     */
    public function supportsUbl(): bool
    {
        return in_array('UBL', $this->supportedFormats ?? []);
    }

    /**
     * Check if the version supports PDF generation.
     */
    public function supportsPdf(): bool
    {
        return in_array('PDF', $this->supportedFormats ?? []);
    }

    /**
     * Check if there's a deprecation notice.
     */
    public function isDeprecated(): bool
    {
        return ! empty($this->deprecationNotice);
    }

    /**
     * Check if an update is required.
     */
    public function isUpdateRequired(): bool
    {
        return ! empty($this->updateRequired);
    }

    /**
     * Get formatted release date.
     */
    public function getFormattedReleaseDate(): string
    {
        if (! $this->releaseDate) {
            return 'Nepoznat datum';
        }

        try {
            $date = new \DateTime($this->releaseDate);

            return $date->format('d.m.Y');
        } catch (\Exception $e) {
            return $this->releaseDate;
        }
    }

    /**
     * Get supported features as a readable string.
     */
    public function getSupportedFeaturesString(): string
    {
        if (empty($this->supportedFeatures)) {
            return 'Nema informacija o podržanim funkcijama';
        }

        return implode(', ', $this->supportedFeatures);
    }
}
