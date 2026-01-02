<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Services\LanguageService;

class SetLanguageMiddleware
{
    public function __construct(
        protected LanguageService $languageService
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale');

        // 1. Check Logged-in User (with validation)
        if (!$locale && auth()->check()) {
            $userLocale = auth()->user()->locale;
            if ($this->languageService->isSupported($userLocale)) {
                $locale = $userLocale;
            }
        }

        // 2. Auto-Detection (if no session/user locale)
        if (!$locale) {
            $detectedLocale = $this->languageService->detectLanguage($request);

            if ($detectedLocale) {
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