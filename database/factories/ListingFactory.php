<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        // Optimization: Use factory helpers instead of DB queries for speed
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'address_id' => function (array $attributes) {
                $userId = $attributes['user_id'];
                $user = User::find($userId);

                if (!$user) {
                    return Address::factory()->create([
                        'addressable_id' => $userId,
                        'addressable_type' => User::class,
                    ])->id;
                }

                return Address::factory()->for($user, 'addressable')->create()->id;
            },

            'status' => $this->faker->randomElement(['active', 'pending', 'expired']),

            'is_featured' => $this->faker->boolean(10),
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),

            'expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),

            'title' => [
                'en' => rtrim($this->faker->sentence(4, true), '.'),
                'de' => $this->faker->realText(50),
            ],
            'description' => [
                'en' => $this->faker->realText(300),
                'de' => $this->faker->realText(300),
            ],
            'type' => \App\Enums\ListingType::DONATION_CAMPAIGN->value,
        ];
    }
}