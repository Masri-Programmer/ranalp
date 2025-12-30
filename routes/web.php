<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NotificationController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('language/{locale}', [LanguageController::class, 'switch'])
    ->whereIn('locale', config('app.supported_locales'))
    ->name('language.switch');

Route::resource('categories', CategoryController::class)->only([
    'index',
    'show'
]);

Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::get('/terms-of-service', [HomeController::class, 'termsOfService'])->name('terms.service');

Route::get('/terms-and-conditions', [HomeController::class, 'termsOfService'])->name('terms.general');
Route::get('/privacy-policy', [HomeController::class, 'termsOfService'])->name('privacy');
Route::get('/imprint', [HomeController::class, 'termsOfService'])->name('imprint');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction:uuid}/receipt', [TransactionController::class, 'receipt'])->name('transactions.receipt');
});

Route::prefix('notifications')->name('notifications.')->group(function () {
        
        Route::get('/', [NotificationController::class, 'index'])
            ->name('index');

        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])
            ->name('mark_all_read');

        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('read');

        Route::delete('/{id}', [NotificationController::class, 'destroy'])
            ->name('destroy');

        Route::post('/settings', [NotificationController::class, 'updateSettings'])
            ->name('settings.update');
    });

Route::middleware(['auth', 'developer'])->prefix('developer')->name('developer.')->group(function () {
    Route::get('/logs', [\App\Http\Controllers\LogViewerController::class, 'index'])->name('logs');
    Route::post('/logs/clear', [\App\Http\Controllers\LogViewerController::class, 'clear'])->name('logs.clear');
    Route::get('/logs/download', [\App\Http\Controllers\LogViewerController::class, 'download'])->name('logs.download');
});

require __DIR__ . '/listings.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/api.php';
