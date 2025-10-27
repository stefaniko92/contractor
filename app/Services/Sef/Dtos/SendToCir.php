<?php

namespace App\Services\Sef\Dtos;

/**
 * Enum for SendToCir options
 */
enum SendToCir: string
{
    case DoNotSend = 'DoNotSend';
    case SendToCir = 'SendToCir';
    case SendOnlyToCir = 'SendOnlyToCir';

    /**
     * Get the description in Serbian.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::DoNotSend => 'Ne slati u CIR',
            self::SendToCir => 'Poslati u CIR i kupcu',
            self::SendOnlyToCir => 'Poslati samo u CIR',
        };
    }

    /**
     * Get the description in English.
     */
    public function getEnglishDescription(): string
    {
        return match ($this) {
            self::DoNotSend => 'Do not send to CIR',
            self::SendToCir => 'Send to CIR and buyer',
            self::SendOnlyToCir => 'Send only to CIR',
        };
    }

    /**
     * Check if invoice will be sent to buyer.
     */
    public function sendsToBuyer(): bool
    {
        return $this === self::SendToCir;
    }

    /**
     * Check if invoice will be sent to CIR.
     */
    public function sendsToCir(): bool
    {
        return $this === self::SendToCir || $this === self::SendOnlyToCir;
    }

    /**
     * Get default option for general use.
     */
    public static function getDefault(): self
    {
        return self::SendToCir;
    }

    /**
     * Create from string value.
     */
    public static function fromString(string $value): self
    {
        return self::tryFrom($value) ?? self::getDefault();
    }

    /**
     * Get all options for select field.
     */
    public static function getSelectOptions(): array
    {
        return [
            self::DoNotSend->value => self::DoNotSend->getDescription(),
            self::SendToCir->value => self::SendToCir->getDescription(),
            self::SendOnlyToCir->value => self::SendOnlyToCir->getDescription(),
        ];
    }
}
