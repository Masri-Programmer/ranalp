<?php

namespace App\Services;

use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class LanguageService
{
    /**
     * Get all supported languages.
     *
     * @return array
     */
    public function getSupportedLanguages(): array
    {
        return config('app.supported_locales', ['en', 'de', 'es', 'fr']);
    }

    /**
     * Check if a language is supported.
     *
     * @param string|null $language
     * @return bool
     */
    public function isSupported(?string $language): bool
    {
        return in_array($language, $this->getSupportedLanguages());
    }

    /**
     * Detect the user's language based on IP or Browser Headers.
     *
     * @param Request $request
     * @return string|null
     */
    public function detectLanguage(Request $request): ?string
    {
        $ip = $request->ip();
        // $ip = '85.214.132.117'; // Uncomment for testing

        $detectedLanguage = null;

        // A. Try IP Location
        if ($position = Location::get($ip)) {
            $code = strtolower($position->countryCode);

            $detectedLanguage = match (true) {
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
        if (!$detectedLanguage) {
            $detectedLanguage = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
        }

        // C. Validate against supported languages
        if ($this->isSupported($detectedLanguage)) {
            return $detectedLanguage;
        }

        return null;
    }

    /**
     * Resolve the best translation from a translatable field.
     *
     * @param array|null $translations
     * @param string|null $locale
     * @param string $fallback
     * @return string
     */
    public function resolveTranslation(?array $translations, ?string $locale = null, string $fallback = ''): string
    {
        if (empty($translations)) {
            return $fallback;
        }

        $locale = $locale ?? app()->getLocale();

        return $translations[$locale]
            ?? $translations['en']
            ?? collect($translations)->first()
            ?? $fallback;
    }
}
