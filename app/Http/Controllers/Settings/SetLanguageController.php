<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Services\LanguageService;

class SetLanguageController extends Controller
{
    public function __invoke(Request $request, LanguageService $languageService): RedirectResponse
    {
        $locale = $request->string('locale');

        if ($languageService->isSupported($locale)) {
            $request->user()->update(['locale' => $locale]);
            // Also update session to immediate effect usually handled by middleware but good for consistency
            session(['locale' => $locale]);
        }

        return back();
    }
}
