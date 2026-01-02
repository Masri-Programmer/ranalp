<?php

use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingFaqController;
use App\Http\Controllers\ListingUpdateController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuctionController;
use App\Http\Controllers\ListingSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::controller(ListingController::class)
    ->prefix('listings')
    ->name('listings.')
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::post('/{listing}/like', 'like')->name('like');

        Route::get('/create', 'create')->name('create');

        Route::get('/{listing}/reviews', [ReviewController::class, 'index'])
            ->name('reviews.index');



        // 🔒 AUTHENTICATED ROUTES
        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/liked', 'liked')->name('liked');

            Route::post('/', 'store')->name('store');

            Route::post('/{listing}/bid', [PaymentController::class, 'bidCheckout'])->name('bid');
            Route::post('/{listing}/donate', [TransactionController::class, 'donate'])->name('donate');

            Route::delete('/{listing}/unlike', 'unlike')->name('unlike');

            Route::get('/{listing}/edit', 'edit')->name('edit');
            Route::match(['put', 'patch'], '/{listing}', 'update')->name('update');
            Route::delete('/{listing}', 'destroy')->name('destroy');

            Route::get('/users/{user}', 'userListings')->name('users.index');

            // FAQ
            Route::post('/{listing}/faq', [ListingFaqController::class, 'store'])->name('faq.store');
            Route::patch('/{listing}/faq/{faq}', [ListingFaqController::class, 'update'])->name('faq.update');
            Route::delete('/{listing}/faq/{faq}', [ListingFaqController::class, 'destroy'])->name('faq.destroy');

            // Updates
            Route::get('{listing}/updates', [ListingUpdateController::class, 'index'])
                ->name('updates.index');
            Route::post('{listing}/updates', [ListingUpdateController::class, 'store'])
                ->name('updates.store');
            Route::put('/updates/{update}', [ListingUpdateController::class, 'update'])
                ->name('updates.update');
            Route::delete('/updates/{update}', [ListingUpdateController::class, 'destroy'])
                ->name('updates.destroy');

            // Reviews
            Route::post('{listing}/reviews', [ReviewController::class, 'store'])
                ->name('reviews.store');


            Route::put('/reviews/{review}', [ReviewController::class, 'update'])
                ->name('reviews.update');

            Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
                ->name('reviews.destroy');

            Route::post('/{listing}/subscribe', [ListingSubscriptionController::class, 'store'])
                ->name('subscribe');


            Route::post('/{listing}/checkout', [PaymentController::class, 'checkout'])->name('checkout');
            Route::get('/{listing}/payment/success', [PaymentController::class, 'success'])->name('success');
        });

        Route::get('/{listing}', 'show')->name('show');
    });
