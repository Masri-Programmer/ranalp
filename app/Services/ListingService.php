<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\PurchaseListing;
use App\Models\AuctionListing;
use App\Models\DonationListing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Listings\StoreListingRequest;
use App\Http\Requests\Listings\UpdateListingRequest;
use App\Settings\GeneralSettings;
use Throwable;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListingService
{
    protected $mediaService;
    public function __construct(
        protected GeneralSettings $settings
    ) {
    }
    /**
     * Filter and paginate listings.
     */
    public function getListings(array $filters, int $perPage = null): LengthAwarePaginator
    {
        $limit = $perPage ?? $this->settings->per_page;

        $listings = Listing::query()
            ->with(['listable', 'user', 'category', 'media'])
            ->filter($filters)
            ->paginate($limit)
            ->withQueryString();

        $listings->getCollection()->transform(function ($listing) {
            $listing->image_url = $listing->getFirstMediaUrl('images');
            unset($listing->media);
            $listing->append('is_liked_by_current_user');
            return $listing;
        });

        return $listings;
    }
    /**
     * Create a new listing.
     * @throws \Exception
     */
    public function createListing(StoreListingRequest $request): Listing
    {
        $validatedData = $request->validated();

        return DB::transaction(function () use ($request, $validatedData) {
            $specificListing = $this->createSpecificListing($validatedData);

            $listing = $specificListing->listing()->create([
                'user_id' => Auth::id(),
                'category_id' => $validatedData['category_id'],
                'expires_at' => $validatedData['expires_at'],
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
            ]);

            $this->mediaService->handleUploads($request, $listing);

            return $listing;
        });
    }

    /**
     * Update an existing listing.
     * @throws \Exception
     */
    public function updateListing(UpdateListingRequest $request, Listing $listing): Listing
    {
        $commonData = $request->getCommonData();
        $specificData = $request->getSpecificData();
        $mediaToDelete = $request->validated('media_to_delete', []);

        return DB::transaction(function () use ($request, $listing, $commonData, $specificData, $mediaToDelete) {
            $listing->update($commonData);

            if ($listing->listable) {
                $listing->listable->update($specificData);
            }

            $this->mediaService->handleDeletions($mediaToDelete);
            $this->mediaService->handleUploads($request, $listing);

            return $listing->fresh();
        });
    }

    private function createSpecificListing(array $data)
    {
        switch ($data['listing_type']) {
            case 'auction':
                return AuctionListing::create([
                    'start_price' => $data['start_price'],
                    'reserve_price' => $data['reserve_price'],
                    'purchase_price' => $data['purchase_price'],
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'],
                ]);
            case 'donation':
                return DonationListing::create([
                    'target' => $data['target'],
                    'is_capped' => $data['is_capped'],
                ]);
            default:
                throw new \Exception("Invalid listing type: {$data['listing_type']}");
        }
    }
}
