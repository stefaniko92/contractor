<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendContactMessageRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ime je obavezno.',
            'name.max' => 'Ime ne može biti duže od 255 karaktera.',
            'email.required' => 'Email adresa je obavezna.',
            'email.email' => 'Email adresa nije validna.',
            'email.max' => 'Email adresa ne može biti duža od 255 karaktera.',
            'subject.max' => 'Naslov ne može biti duži od 255 karaktera.',
            'message.required' => 'Poruka je obavezna.',
            'message.min' => 'Poruka mora imati najmanje 10 karaktera.',
            'message.max' => 'Poruka ne može biti duža od 5000 karaktera.',
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
