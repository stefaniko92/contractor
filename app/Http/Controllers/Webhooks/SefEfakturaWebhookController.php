<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\SefEfakturaSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SefEfakturaWebhookController extends Controller
{
    /**
     * Handle webhook from SEF/EFaktura service
     */
    public function handle(Request $request, int $userId): JsonResponse
    {
        try {
            // Verify user exists
            $user = User::findOrFail($userId);

            // Get the settings for this user
            $settings = SefEfakturaSetting::where('user_id', $userId)->firstOrFail();

            // Verify webhook token
            if (! $this->verifyToken($request, $userId, $settings)) {
                Log::warning("Invalid webhook token for user {$userId}");

                return response()->json(['success' => false, 'error' => 'Invalid token'], 401);
            }

            // Process the webhook payload
            $payload = $request->all();

            Log::info("SEF/EFaktura webhook received for user {$userId}", [
                'service_type' => $settings->service_type,
                'event_type' => $payload['event_type'] ?? null,
                'invoice_id' => $payload['invoice_id'] ?? null,
            ]);

            // Handle different event types
            $this->processWebhookEvent($user, $settings, $payload);

            // Update last webhook test timestamp
            $settings->update(['last_webhook_test' => now()]);

            return response()->json(['success' => true, 'message' => 'Webhook processed successfully']);
        } catch (\Exception $e) {
            Log::error("SEF/EFaktura webhook error for user {$userId}: ".$e->getMessage());

            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Verify the webhook token
     */
    private function verifyToken(Request $request, int $userId, SefEfakturaSetting $settings): bool
    {
        $token = $request->query('token');

        if (! $token) {
            return false;
        }

        // Generate expected token
        $expectedToken = hash('sha256', $userId.($settings->created_at?->timestamp ?? time()));

        return hash_equals($token, $expectedToken);
    }

    /**
     * Process webhook event
     */
    private function processWebhookEvent(User $user, SefEfakturaSetting $settings, array $payload): void
    {
        $eventType = $payload['event_type'] ?? null;
        $invoiceId = $payload['invoice_id'] ?? null;
        $status = $payload['status'] ?? null;

        // Find the invoice
        if ($invoiceId) {
            $invoice = Invoice::where('user_id', $user->id)
                ->where('id', $invoiceId)
                ->first();

            if ($invoice) {
                $this->handleInvoiceUpdate($invoice, $settings, $payload);
            }
        }

        // Log the event
        $integrationData = $settings->integration_data ?? [];
        $integrationData['last_webhook_event'] = [
            'timestamp' => now()->toIso8601String(),
            'type' => $eventType,
            'status' => $status,
            'invoice_id' => $invoiceId,
        ];
        $settings->update(['integration_data' => $integrationData]);
    }

    /**
     * Handle invoice update from webhook
     */
    private function handleInvoiceUpdate(Invoice $invoice, SefEfakturaSetting $settings, array $payload): void
    {
        // Update invoice with webhook data if needed
        // This can be extended based on your integration needs

        $status = $payload['status'] ?? null;
        $externalId = $payload['external_id'] ?? null;
        $reference = $payload['reference'] ?? null;

        if ($externalId) {
            // Store the external reference
            $integrationData = $invoice->integrationData ?? [];
            $integrationData['sef_efaktura'] = [
                'external_id' => $externalId,
                'reference' => $reference,
                'status' => $status,
                'updated_at' => now()->toIso8601String(),
            ];
        }

        Log::info("Invoice {$invoice->id} updated via webhook", [
            'status' => $status,
            'external_id' => $externalId,
        ]);
    }
}
