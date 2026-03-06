<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GeneratePublicInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public API endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Email
            'email' => ['required', 'email:rfc', 'max:255'],

            // Invoice type
            'invoice_type' => ['required', 'in:domaca,inostrana'],

            // Seller data (becomes UserCompany)
            'seller.pib' => ['required', 'digits:9'],
            'seller.mb' => ['nullable', 'digits:8'],
            'seller.company_name' => ['required', 'string', 'max:255'],
            'seller.address' => ['required', 'string', 'max:255'],
            'seller.city' => ['nullable', 'string', 'max:100'],
            'seller.phone' => ['nullable', 'string', 'max:50'],

            // Buyer data (becomes Client)
            'buyer.name' => ['required', 'string', 'max:255'],
            'buyer.pib' => ['nullable', 'string', 'max:20'],
            'buyer.address' => ['required', 'string', 'max:255'],
            'buyer.city' => ['nullable', 'string', 'max:100'],
            'buyer.country' => ['nullable', 'string', 'max:100'],

            // Invoice data
            'invoice.number' => ['nullable', 'string', 'max:50'],
            'invoice.date_issued' => ['required', 'date', 'date_format:Y-m-d'],
            'invoice.date_due' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:invoice.date_issued'],
            'invoice.place' => ['required', 'string', 'max:255'],
            'invoice.currency' => ['required', 'in:RSD,EUR,USD,CHF,GBP'],
            'invoice.note' => ['nullable', 'string', 'max:1000'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', 'in:usluga,proizvod'],
            'items.*.unit' => ['required', 'in:komad,sat,mesec,dan,paušal'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:%,currency'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email adresa je obavezna.',
            'email.email' => 'Email adresa nije validna.',
            'invoice_type.required' => 'Tip fakture je obavezan.',
            'invoice_type.in' => 'Tip fakture mora biti "domaca" ili "inostrana".',
            'seller.pib.required' => 'PIB prodavca je obavezan.',
            'seller.pib.digits' => 'PIB mora imati tačno 9 cifara.',
            'seller.mb.digits' => 'Matični broj mora imati tačno 8 cifara.',
            'seller.company_name.required' => 'Naziv kompanije prodavca je obavezan.',
            'seller.address.required' => 'Adresa prodavca je obavezna.',
            'buyer.name.required' => 'Ime kupca je obavezno.',
            'buyer.address.required' => 'Adresa kupca je obavezna.',
            'invoice.date_issued.required' => 'Datum izdavanja fakture je obavezan.',
            'invoice.date_issued.date_format' => 'Datum izdavanja mora biti u formatu Y-m-d.',
            'invoice.date_due.required' => 'Datum dospeća je obavezan.',
            'invoice.date_due.date_format' => 'Datum dospeća mora biti u formatu Y-m-d.',
            'invoice.date_due.after_or_equal' => 'Datum dospeća mora biti posle ili jednak datumu izdavanja.',
            'invoice.place.required' => 'Mesto prometa je obavezno.',
            'invoice.currency.required' => 'Valuta je obavezna.',
            'invoice.currency.in' => 'Valuta mora biti jedna od: RSD, EUR, USD, CHF, GBP.',
            'items.required' => 'Stavke su obavezne.',
            'items.min' => 'Mora postojati bar jedna stavka.',
            'items.*.title.required' => 'Naziv stavke je obavezan.',
            'items.*.type.required' => 'Tip stavke je obavezan.',
            'items.*.type.in' => 'Tip stavke mora biti "usluga" ili "proizvod".',
            'items.*.unit.required' => 'Jedinica mere je obavezna.',
            'items.*.unit.in' => 'Jedinica mere mora biti: komad, sat, mesec, dan ili paušal.',
            'items.*.quantity.required' => 'Količina je obavezna.',
            'items.*.quantity.numeric' => 'Količina mora biti broj.',
            'items.*.quantity.min' => 'Količina mora biti veća od 0.',
            'items.*.unit_price.required' => 'Jedinična cena je obavezna.',
            'items.*.unit_price.numeric' => 'Jedinična cena mora biti broj.',
            'items.*.discount_type.in' => 'Tip popusta mora biti "%" ili "currency".',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error' => 'Validation error',
                'details' => $validator->errors(),
            ], 422)
        );
    }
}
