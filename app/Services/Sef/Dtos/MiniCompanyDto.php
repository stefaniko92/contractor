<?php

namespace App\Services\Sef\Dtos;

class MiniCompanyDto
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $taxIdentifier,
        public readonly ?string $name,
        public readonly ?string $accountNumber,
        public readonly ?int $bankId,
        public readonly ?string $pib,
        public readonly ?string $mb,
        public readonly ?bool $isActive,
        public readonly ?bool $isRegisteredForVat,
        public readonly ?bool $isRegisteredForDigitalVat,
        public readonly ?string $countryIsoCode,
        public readonly ?string $countryName,
        public readonly ?string $cityName,
        public readonly ?string $streetName,
        public readonly ?string $streetNumber,
        public readonly ?int $typeId,
        public readonly ?string $typeName,
        public readonly ?int $legalFormTypeId,
        public readonly ?string $legalFormTypeName,
        public readonly ?bool $isResident,
        public readonly ?bool $isRegisteredForSmallBusinessTax,
        public readonly ?bool $isRegisteredForQuarterlyVat,
        public readonly ?string $vatExemptionReasonId,
        public readonly ?string $vatExemptionReasonText,
        public readonly ?string $vatExemptionReasonStartDate,
        public readonly ?string $vatExemptionReasonEndDate,
        public readonly ?bool $isSefEnabled,
        public readonly ?string $sefActivationDate,
        public readonly ?string $sefDeactivationDate,
        public readonly ?bool $isSeiEnabled,
        public readonly ?string $seiActivationDate,
        public readonly ?string $seiDeactivationDate,
        public readonly ?string $sefCode,
        public readonly ?bool $isNonResidentBank,
        public readonly ?bool $hasNonResidentBankAccount,
        public readonly ?string $swiftCode,
        public readonly ?string $nonResidentBankName,
        public readonly ?string $nonResidentBankCountryName,
        public readonly ?bool $isResidentBank,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['Id'] ?? null,
            taxIdentifier: $data['TaxIdentifier'] ?? null,
            name: $data['Name'] ?? null,
            accountNumber: $data['AccountNumber'] ?? null,
            bankId: $data['BankId'] ?? null,
            pib: $data['PIB'] ?? null,
            mb: $data['MB'] ?? null,
            isActive: $data['IsActive'] ?? null,
            isRegisteredForVat: $data['IsRegisteredForVat'] ?? null,
            isRegisteredForDigitalVat: $data['IsRegisteredForDigitalVat'] ?? null,
            countryIsoCode: $data['CountryIsoCode'] ?? null,
            countryName: $data['CountryName'] ?? null,
            cityName: $data['CityName'] ?? null,
            streetName: $data['StreetName'] ?? null,
            streetNumber: $data['StreetNumber'] ?? null,
            typeId: $data['TypeId'] ?? null,
            typeName: $data['TypeName'] ?? null,
            legalFormTypeId: $data['LegalFormTypeId'] ?? null,
            legalFormTypeName: $data['LegalFormTypeName'] ?? null,
            isResident: $data['IsResident'] ?? null,
            isRegisteredForSmallBusinessTax: $data['IsRegisteredForSmallBusinessTax'] ?? null,
            isRegisteredForQuarterlyVat: $data['IsRegisteredForQuarterlyVat'] ?? null,
            vatExemptionReasonId: $data['VatExemptionReasonId'] ?? null,
            vatExemptionReasonText: $data['VatExemptionReasonText'] ?? null,
            vatExemptionReasonStartDate: $data['VatExemptionReasonStartDate'] ?? null,
            vatExemptionReasonEndDate: $data['VatExemptionReasonEndDate'] ?? null,
            isSefEnabled: $data['IsSefEnabled'] ?? null,
            sefActivationDate: $data['SefActivationDate'] ?? null,
            sefDeactivationDate: $data['SefDeactivationDate'] ?? null,
            isSeiEnabled: $data['IsSeiEnabled'] ?? null,
            seiActivationDate: $data['SeiActivationDate'] ?? null,
            seiDeactivationDate: $data['SeiDeactivationDate'] ?? null,
            sefCode: $data['SefCode'] ?? null,
            isNonResidentBank: $data['IsNonResidentBank'] ?? null,
            hasNonResidentBankAccount: $data['HasNonResidentBankAccount'] ?? null,
            swiftCode: $data['SwiftCode'] ?? null,
            nonResidentBankName: $data['NonResidentBankName'] ?? null,
            nonResidentBankCountryName: $data['NonResidentBankCountryName'] ?? null,
            isResidentBank: $data['IsResidentBank'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tax_identifier' => $this->taxIdentifier,
            'name' => $this->name,
            'account_number' => $this->accountNumber,
            'bank_id' => $this->bankId,
            'pib' => $this->pib,
            'mb' => $this->mb,
            'is_active' => $this->isActive,
            'is_registered_for_vat' => $this->isRegisteredForVat,
            'is_registered_for_digital_vat' => $this->isRegisteredForDigitalVat,
            'country_iso_code' => $this->countryIsoCode,
            'country_name' => $this->countryName,
            'city_name' => $this->cityName,
            'street_name' => $this->streetName,
            'street_number' => $this->streetNumber,
            'type_id' => $this->typeId,
            'type_name' => $this->typeName,
            'legal_form_type_id' => $this->legalFormTypeId,
            'legal_form_type_name' => $this->legalFormTypeName,
            'is_resident' => $this->isResident,
            'is_registered_for_small_business_tax' => $this->isRegisteredForSmallBusinessTax,
            'is_registered_for_quarterly_vat' => $this->isRegisteredForQuarterlyVat,
            'vat_exemption_reason_id' => $this->vatExemptionReasonId,
            'vat_exemption_reason_text' => $this->vatExemptionReasonText,
            'vat_exemption_reason_start_date' => $this->vatExemptionReasonStartDate,
            'vat_exemption_reason_end_date' => $this->vatExemptionReasonEndDate,
            'is_sef_enabled' => $this->isSefEnabled,
            'sef_activation_date' => $this->sefActivationDate,
            'sef_deactivation_date' => $this->sefDeactivationDate,
            'is_sei_enabled' => $this->isSeiEnabled,
            'sei_activation_date' => $this->seiActivationDate,
            'sei_deactivation_date' => $this->seiDeactivationDate,
            'sef_code' => $this->sefCode,
            'is_non_resident_bank' => $this->isNonResidentBank,
            'has_non_resident_bank_account' => $this->hasNonResidentBankAccount,
            'swift_code' => $this->swiftCode,
            'non_resident_bank_name' => $this->nonResidentBankName,
            'non_resident_bank_country_name' => $this->nonResidentBankCountryName,
            'is_resident_bank' => $this->isResidentBank,
        ];
    }

    /**
     * Check if the company is SEF enabled.
     */
    public function isSefEnabled(): bool
    {
        return (bool) $this->isSefEnabled;
    }

    /**
     * Get the company's PIB (Tax Identification Number).
     */
    public function getPib(): ?string
    {
        return $this->pib;
    }

    /**
     * Get the company's full address.
     */
    public function getFullAddress(): string
    {
        $address = '';

        if ($this->streetName) {
            $address .= $this->streetName;
        }

        if ($this->streetNumber) {
            $address .= ' '.$this->streetNumber;
        }

        if ($this->cityName) {
            $address .= $address ? ', '.$this->cityName : $this->cityName;
        }

        if ($this->countryName && $this->countryName !== 'Srbija') {
            $address .= $address ? ', '.$this->countryName : $this->countryName;
        }

        return $address;
    }
}
