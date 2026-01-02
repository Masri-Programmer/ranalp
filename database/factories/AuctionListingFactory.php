<?php

namespace Database\Factories;

use App\Models\AuctionListing;
use App\Models\Listing;
use App\Enums\ListingType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuctionListingFactory extends Factory
{
    protected $model = AuctionListing::class;

    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('-1 month', '+1 week');

        return [
            'start_price' => $this->faker->randomFloat(2, 10, 1000),
            'reserve_price' => $this->faker->boolean(30) ? $this->faker->randomFloat(2, 500, 2000) : null,
            'purchase_price' => $this->faker->boolean(20) ? $this->faker->randomFloat(2, 2000, 5000) : null,
            'current_bid' => null, // Starts empty
            'item_condition' => $this->faker->randomElement(['new', 'used', 'refurbished']),
            'starts_at' => $startsAt,
            'ends_at' => $this->faker->dateTimeBetween($startsAt, '+1 month'),
        ];
    }

    /**
     * The magic method called by your Seeder.
     * It creates the parent Listing immediately after the Auction is created.
     */
    public function withListing(): static
    {
        return $this->afterCreating(function (AuctionListing $auction) {
            Listing::factory()->create([
                'listable_type' => AuctionListing::class,
                'listable_id' => $auction->id,
                'type' => ListingType::CHARITY_ACTION->value,

                // CRITICAL: Sync the Listing expiration with the Auction end time
                'expires_at' => $auction->ends_at,
                'published_at' => $auction->starts_at,
            ]);
        });
    }
}