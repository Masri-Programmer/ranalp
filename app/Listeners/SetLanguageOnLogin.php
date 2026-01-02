<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Services\LanguageService;

class SetLanguageOnLogin
{
    public function __construct(
        protected LanguageService $languageService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        $locale = $this->languageService->detectLanguage(request());

        if ($locale) {
            // Only update if different and valid
            if ($user->locale !== $locale && $this->languageService->isSupported($locale)) {
                $user->locale = $locale;
                $user->save();
            }

            Session::put('locale', $locale);
        }
    }
}
