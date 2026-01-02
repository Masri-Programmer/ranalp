<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

use App\Services\LanguageService;

class LanguageController extends Controller
{
    /**
     * Switch the application locale and redirect back.
     */
    public function switch(string $locale, Request $request, LanguageService $languageService): RedirectResponse
    {
        if ($languageService->isSupported($locale)) {
            Session::put('locale', $locale);
            App::setLocale($locale);

            if ($request->user()) {
                $request->user()->update(['locale' => $locale]);
            }
        }

        return redirect()->back();
    }
}
