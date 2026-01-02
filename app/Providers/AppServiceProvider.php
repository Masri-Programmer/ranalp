<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Providers\FakeMediaProvider;
use Faker\Factory as FakerFactory;
use App\Models\Listing;
use App\Observers\ListingObserver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom Faker Provider for media URLs only when seeding
        if ($this->app->environment('local', 'staging') && class_exists(FakerFactory::class)) {
            $this->app->singleton(\Faker\Generator::class, function () {
                $faker = FakerFactory::create();
                $faker->addProvider(new FakeMediaProvider($faker));
                return $faker;
            });
        }
        Listing::observe(ListingObserver::class);
        \App\Models\ListingReview::observe(\App\Observers\ListingReviewObserver::class);
        \App\Models\ListingFaq::observe(\App\Observers\ListingFaqObserver::class);
        \App\Models\AuctionListing::observe(\App\Observers\AuctionListingObserver::class);
        \App\Models\DonationListing::observe(\App\Observers\DonationListingObserver::class);

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\SetLanguageOnLogin::class
        );

        \Illuminate\Auth\Notifications\VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject(__('messages.auth.email_verification.subject', ['app' => config('app.name')]))
                ->greeting(__('messages.auth.email_verification.greeting', ['name' => $notifiable->name, 'app' => config('app.name')]))
                ->line(__('messages.auth.email_verification.line1', ['app' => config('app.name')]))
                ->action(__('messages.auth.email_verification.button', ['app' => config('app.name')]), $url)
                ->line(__('messages.auth.email_verification.line2', ['app' => config('app.name')]))
                ->salutation(__('messages.auth.email_verification.salutation', ['app' => config('app.name')]));
        });
    }
}
