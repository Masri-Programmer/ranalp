<?php

namespace App\Http\Controllers;

use App\Services\ListingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Settings\GeneralSettings;
class HomeController extends Controller
{
    protected ListingService $listingService;
    protected GeneralSettings $settings;

    public function __construct(ListingService $listingService, GeneralSettings $settings)
    {
        $this->listingService = $listingService;
        $this->settings = $settings;
    }

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'types' => 'nullable|string',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'city' => 'nullable|string|max:100',
            'sort' => 'nullable|string|in:latest,oldest,price-low,price-high,popular',
        ]);

        $listings = $this->listingService->getListings($filters, $this->settings->home_per_page);

        return Inertia::render('Homepage', [
            'listings' => $listings,
            'filters' => $filters,
        ]);
    }

    public function faq(): Response
    {
        return Inertia::render('Faq');
    }

    public function about(): Response
    {
        return Inertia::render('About');
    }

    public function termsOfService(): Response
    {
        return Inertia::render('legal/TermsOfService');
    }

    public function contact(): Response
    {
        return Inertia::render('Contact');
    }
}
