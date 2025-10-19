<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['sr', 'en', 'ru'];

        // Prioritet: URL parametar, zatim user preference, zatim session
        $locale = $request->get('lang');

        // Check authenticated user's language preference
        if (! $locale && auth()->check() && auth()->user()->language) {
            $locale = auth()->user()->language;
        }

        if (! $locale) {
            $locale = session('locale');
        }

        // Ili iz route parametra
        if (! $locale && $request->route('locale')) {
            $locale = $request->route('locale');
        }

        // Ili iz browser preferences
        if (! $locale) {
            $locale = $request->getPreferredLanguage($supportedLocales);
        }

        // Default fallback
        if (! $locale || ! in_array($locale, $supportedLocales)) {
            $locale = config('app.locale', 'sr');
        }

        app()->setLocale($locale);
        session(['locale' => $locale]);

        return $next($request);
    }
}
