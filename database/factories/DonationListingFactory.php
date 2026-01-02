<?php

namespace Database\Factories;

use App\Models\DonationListing;
use App\Models\Listing;
use App\Enums\ListingType;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationListingFactory extends Factory
{
    protected $model = DonationListing::class;

    public function definition(): array
    {
        return [
            'target' => $this->faker->randomFloat(2, 1000, 50000),
            'raised' => 0,
            'donors_count' => 0,
            'is_capped' => $this->faker->boolean(10),
            'requires_verification' => $this->faker->boolean(50),
        ];
    }

    public function withListing(): static
    {
        return $this->afterCreating(function (DonationListing $donation) {
            Listing::factory()->create([
                'listable_type' => DonationListing::class,
                'listable_id' => $donation->id,
                'type' => $this->faker->randomElement([
                    ListingType::PRIVATE_OCCASION->value,
                    ListingType::DONATION_CAMPAIGN->value,
                    ListingType::FOUNDERS_CREATIVES->value
                ]),
                // Donations might expire whenever, or match a campaign end date
            ]);
        });
    }
}