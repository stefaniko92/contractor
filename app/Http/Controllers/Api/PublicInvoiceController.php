<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeneratePublicInvoiceRequest;
use App\Services\PublicInvoiceService;
use Illuminate\Http\JsonResponse;

class PublicInvoiceController extends Controller
{
    public function __construct(
        private readonly PublicInvoiceService $invoiceService
    ) {}

    /**
     * Generate a public invoice and send via email
     */
    public function generate(GeneratePublicInvoiceRequest $request): JsonResponse
    {
        try {
            $result = $this->invoiceService->handle($request->validated());

            return response()->json([
                'success' => true,
                'message' => "Faktura je poslata na {$request->input('email')}.",
                'user_created' => $result['user_created'],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Public invoice generation failed', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Greška prilikom kreiranja fakture.',
                'message' => config('app.debug') ? $e->getMessage() : 'Molimo pokušajte ponovo.',
            ], 500);
        }
    }
}
