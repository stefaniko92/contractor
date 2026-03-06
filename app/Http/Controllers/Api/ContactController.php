<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendContactMessageRequest;
use App\Mail\ContactFormMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Send contact form message via email
     */
    public function send(SendContactMessageRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Get recipient email from config
            $recipientEmail = config('mail.contact_recipient', 'stefanrakic92@gmail.com');

            // Send email
            Mail::to($recipientEmail)->send(
                new ContactFormMessage(
                    $data['name'],
                    $data['email'],
                    $data['subject'] ?? 'Nova poruka sa kontakt forme',
                    $data['message']
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Hvala! Vaša poruka je uspešno poslata. Odgovorićemo vam u najkraćem roku.',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Contact form submission failed', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Greška prilikom slanja poruke.',
                'message' => config('app.debug') ? $e->getMessage() : 'Molimo pokušajte ponovo.',
            ], 500);
        }
    }
}
