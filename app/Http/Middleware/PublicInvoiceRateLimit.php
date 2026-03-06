<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PublicInvoiceRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');

        // If no email, let validation handle it
        if (! $email) {
            return $next($request);
        }

        $cacheKey = 'public_invoice_count:'.md5(strtolower($email));
        $ttl = 30 * 24 * 60 * 60; // 30 days in seconds
        $maxInvoices = 3;

        // Get current count
        $count = Cache::get($cacheKey, 0);

        if ($count >= $maxInvoices) {
            return response()->json([
                'success' => false,
                'error' => 'Dostigli ste maksimalan broj besplatnih faktura (3) u zadnjih 30 dana.',
                'message' => 'Registrujte se za neograničeno kreiranje faktura.',
            ], 429);
        }

        // Increment counter
        Cache::put($cacheKey, $count + 1, $ttl);

        return $next($request);
    }
}
