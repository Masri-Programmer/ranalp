<?php

namespace Database\Seeders;

use App\Models\AuctionListing;
use App\Models\DonationListing;
use App\Models\Listing;
use App\Models\ListingFaq;
use App\Models\ListingReview;
use App\Models\User;
use App\Models\Category;
use App\Enums\ListingType;
use Illuminate\Database\Seeder;
use Database\Factories\AddressFactory;

// Assuming you have this package, otherwise extend standard Seeder
use Kdabrow\SeederOnce\SeederOnce;

class ListingSeeder extends SeederOnce
{
    protected int $count = 3;

    public function run(): void
    {
        if (!User::count() || !Category::count()) {
            $this->command->error('Please seed users and categories first.');
            return;
        }

        $this->command->info("Seeding {$this->count} of each listing type...");

        $listingTypes = [
            AuctionListing::class,
            DonationListing::class,
        ];

        foreach ($listingTypes as $class) {
            $this->command->info("Creating $class...");

            // 1. Run the Factory (which runs withListing() -> creates parent Listing)
            $items = $class::factory()
                ->count($this->count)
                ->withListing() // Calls the method we added above
                ->create();

            // 2. Loop through created items to add Extras (Review/Media)
            foreach ($items as $item) {
                // Because we used 'afterCreating', the relationship might not be loaded yet
                $item->load('listing');

                if ($item->listing) {
                    $this->seedExtras($item->listing);
                }
            }
        }

        $this->command->info('Random listings seeded.');
        $this->seedOriginalListings();
    }

    protected function seedOriginalListings(): void
    {
        $user = User::first();
        $category = Category::first();

        // Ensure user has an address
        $address = $user->addresses()->first()
            ?? \App\Models\Address::factory()->for($user, 'addressable')->create();

        // --- 1. Manual Auction ---
        $auction = AuctionListing::create([
            'start_price' => 75000,
            'item_condition' => 'new',
            'starts_at' => now(),
            'ends_at' => now()->addWeeks(2),
        ]);

        // MorphOne create: Automatically sets listable_id & listable_type
        $listing1 = $auction->listing()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'address_id' => $address->id,
            'type' => ListingType::CHARITY_ACTION->value,
            'status' => 'active',
            'expires_at' => $auction->ends_at, // Sync Dates!
            'title' => ['en' => 'Rare Collectible Art', 'en' => 'Rare Collectible Art', 'de' => 'Seltene Kunst'],
            'description' => ['en' => 'Bidding starts now...', 'de' => 'Das Bieten beginnt...'],
        ]);

        $this->seedExtras($listing1);

        // --- 2. Manual Donation ---
        $donation = DonationListing::create([
            'target' => 25000,
            'raised' => 0
        ]);

        $listing2 = $donation->listing()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'address_id' => $address->id,
            'type' => ListingType::DONATION_CAMPAIGN->value,
            'status' => 'active',
            'expires_at' => now()->addMonths(1),
            'title' => ['en' => 'Park Renovation', 'de' => 'Parkrenovierung'],
            'description' => ['en' => 'Help us renovate...', 'de' => 'Helfen Sie uns...'],
        ]);


        $this->seedExtras($listing2);

        $this->command->info('Original listings seeded.');
    }

    /**
     * Grouped the extra seeders into one helper for cleaner code
     */
    private function seedExtras(Listing $listing): void
    {
        $this->seedFaqs($listing);
        $this->seedReviews($listing);
        $this->seedMedia($listing);
    }

    private function seedFaqs(Listing $listing): void
    {
        ListingFaq::factory()->count(2)->create([
            'listing_id' => $listing->id,
            // Simple check to avoid getting the same user
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
        ]);
    }

    private function seedReviews(Listing $listing): void
    {
        ListingReview::factory()->count(rand(1, 3))->create([
            'listing_id' => $listing->id,
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
        ]);
    }

    private function seedMedia(Listing $listing): void
    {
        $seedingPath = storage_path('app/seeding');
        if (!file_exists($seedingPath)) {
            mkdir($seedingPath, 0755, true);
        }

        $files = [
            'image_1.jpg' => 'https://loremflickr.com/640/480/business',
            'image_2.jpg' => 'https://loremflickr.com/640/480/city',
            'video.mp4' => 'https://raw.githubusercontent.com/intel-iot-devkit/sample-videos/master/classroom.mp4',
            'doc_1.pdf' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'doc_2.pdf' => 'https://www.africau.edu/images/default/sample.pdf',
        ];

        // Download files once if they don't exist
        foreach ($files as $name => $url) {
            $path = "$seedingPath/$name";
            if (!file_exists($path)) {
                try {
                    file_put_contents($path, file_get_contents($url));
                } catch (\Exception $e) {
                    // Fallback or ignore if download fails
                    continue;
                }
            }
        }

        try {
            if (file_exists("$seedingPath/image_1.jpg")) {
                $listing->addMedia("$seedingPath/image_1.jpg")
                    ->preservingOriginal()
                    ->toMediaCollection('images');
            }

            if (file_exists("$seedingPath/image_2.jpg")) {
                $listing->addMedia("$seedingPath/image_2.jpg")
                    ->preservingOriginal()
                    ->toMediaCollection('images');
            }
        } catch (\Exception $e) {
        }

        try {
            // Only add video to some listings
            if (rand(0, 1) && file_exists("$seedingPath/video.mp4")) {
                $listing->addMedia("$seedingPath/video.mp4")
                    ->preservingOriginal()
                    ->toMediaCollection('videos');
            }
        } catch (\Exception $e) {
        }

        try {
            if (file_exists("$seedingPath/doc_1.pdf")) {
                $listing->addMedia("$seedingPath/doc_1.pdf")
                    ->preservingOriginal()
                    ->withCustomProperties(['name' => 'Generic Business Plan'])
                    ->toMediaCollection('documents');
            }

            if (file_exists("$seedingPath/doc_2.pdf")) {
                $listing->addMedia("$seedingPath/doc_2.pdf")
                    ->preservingOriginal()
                    ->withCustomProperties(['name' => 'Annual Report'])
                    ->toMediaCollection('documents');
            }
        } catch (\Exception $e) {
        }
    }
}