<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Stevebauman\Location\Facades\Location;

class SetLanguageMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $supportedLocales = config('app.supported_locales', ['en', 'de', 'es', 'fr']);

        $locale = Session::get('locale');

        // 1. Check Logged-in User (with validation)
        if (!$locale && auth()->check()) {
            $userLocale = auth()->user()->locale;
            if (in_array($userLocale, $supportedLocales)) {
                $locale = $userLocale;
            }
        }

        // 2. Auto-Detection (if no session/user locale)
        if (!$locale) {
            $ip = $request->ip();
            // $ip = '134.119.219.165'; // Uncomment for testing

            $detectedLocale = null;

            // A. Try IP Location
            if ($position = Location::get($ip)) {
                $code = strtolower($position->countryCode);

                $detectedLocale = match (true) {
                    // German Speaking
                    in_array($code, ['de', 'at', 'ch', 'li', 'lu']) => 'de',

                    // Spanish Speaking
                    in_array($code, [
                        'es',
                        'mx',
                        'co',
                        'ar',
                        'pe',
                        've',
                        'cl',
                        'ec',
                        'gt',
                        'cu',
                        'bo',
                        'do',
                        'hn',
                        'py',
                        'sv',
                        'ni',
                        'cr',
                        'pa',
                        'uy',
                        'gq'
                    ]) => 'es',

                    // English Speaking
                    in_array($code, ['us', 'gb', 'ca', 'au', 'nz', 'ie', 'za', 'sg', 'ph']) => 'en',

                    // Return NULL for unknown countries (e.g. France, Japan)
                    // This allows us to try the Browser Language next!
                    default => null,
                };
            }

            // B. If IP didn't yield a result, try Browser Headers
            if (!$detectedLocale) {
                $detectedLocale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            }

            // C. Validate and Save
            if (in_array($detectedLocale, $supportedLocales)) {
                $locale = $detectedLocale;
                Session::put('locale', $locale);
            }
        }

        // 3. Final Fallback
        if (!$locale) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}