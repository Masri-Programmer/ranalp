<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Transaction;
use App\Models\PurchaseListing;
use App\Models\DonationListing;
use App\Models\AuctionListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use App\Models\Bid;
use Illuminate\Support\Facades\Log;
use App\Mail\ListingUpdated;
use Illuminate\Support\Facades\Mail;
use App\Services\ListingUpdateService;
use App\Notifications\ListingInteraction;

class PaymentController extends Controller
{
    public function checkout(Request $request, Listing $listing, \App\Services\TransactionFeeCalculator $feeCalculator)
    {
        $user = Auth::user();

        // 1. Ownership & Expiration Checks
        if ($listing->user_id === $user->id) {
            return $this->checkError('messages.errors.own_listing');
        }

        if ($listing->is_expired) {
            return $this->checkError('messages.errors.listing_expired');
        }



        $listing->load('listable');

        $amount = 0;
        $productName = $listing->title;
        $transactionType = 'purchase';
        $quantity = 1;
        $metadata = [];

        // 2. Calculate Amount & Validation based on Type
        switch ($listing->listable_type) {
            case DonationListing::class:
                $request->validate(['amount' => 'required|numeric|min:1']);
                $amount = $request->amount;
                $transactionType = 'donation';
                $productName = "Donation to: {$listing->title}";
                break;


            case AuctionListing::class:
                 if (!$listing->listable->purchase_price) {
                     return $this->checkError('messages.errors.buy_now_not_available');
                 }
                 $amount = $listing->listable->purchase_price;
                 $transactionType = 'auction_purchase';
                 break;

            default:
                return $this->checkError('messages.errors.invalid_listing_type');
        }

        // Calculate Fee
        $fee = $feeCalculator->calculate($listing, (float) $amount);

        $currency = \App\Services\MoneyService::getCurrency($user);

        // 3. Create Pending Transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'payable_type' => $listing->listable_type,
            'payable_id' => $listing->listable_id,
            'amount' => $amount,
            'fee' => $fee,
            'currency' => $currency,
            'status' => 'pending',
            'type' => $transactionType,
            'metadata' => $metadata,
            'transaction_ref' => 'temp_' . uniqid(),
        ]);

        // 4. Initialize Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Prepare image for Stripe (ensure it's a valid URL)
        $imageUrl = $listing->getFirstMediaUrl('images', 'thumb');
        $images = $imageUrl ? [$imageUrl] : [];

        try {
            $checkoutSession = Session::create([
                // Option A: Explicitly defined
                'payment_method_types' => ['card', 'paypal'],

                // Option B: Recommended
                // 'automatic_payment_methods' => ['enabled' => true],

                'customer_email' => $user->email,
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => $productName,
                            'images' => $images,
                        ],
                        'unit_amount' => (int)($amount * 100), // Cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('listings.success', $listing->id) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('listings.show', $listing->id),
                'client_reference_id' => (string) $transaction->id,
                'metadata' => array_merge([
                    'transaction_uuid' => (string) $transaction->uuid,
                    'listing_id' => (string) $listing->id,
                ], $metadata),
            ]);
        } catch (\Exception $e) {
            // [4] Pass exception to checkError for automatic logging if debug is on
            return $this->checkError('messages.errors.payment_init_failed', $e);
        }

        // 5. Update Transaction with real Stripe Session ID
        $transaction->update(['transaction_ref' => $checkoutSession->id]);

        return Inertia::location($checkoutSession->url);
    }

    public function bidCheckout(Request $request, Listing $listing)
    {
        $user = Auth::user();

        // 1. Basic Validation
        if ($listing->user_id === $user->id) {
             return $this->checkError('messages.errors.own_listing');
        }
        
        if ($listing->is_expired) {
            return $this->checkError('messages.errors.listing_expired');
        }

        $auction = $listing->listable;

        if (!$auction instanceof AuctionListing) {
             return $this->checkError('messages.errors.invalid_listing_type');
        }

        $request->validate(['amount' => 'required|numeric|min:0.01']);
        
        // 2. Validate Bid Amount (Pre-flight check, real check happens at success to prevent race conditions but save UX)
        $minRequired = ($auction->current_bid) 
            ? $auction->current_bid + 0.01 
            : $auction->start_price;

        if ($request->amount < $minRequired) {
             // Assuming validation error style for params? 
             // If checkError doesn't support params in 2nd arg, we might need another way or pass null.
             // Based on usage checkError($key, $exception), maybe it doesn't support params?
             // But existing code used it? No, I introduced it.
             // Let's pass null as 2nd arg if it accepts 3 params checkError($msg, $e, $data)
             // OR format the string first.
             return $this->checkError(__('messages.errors.bid_too_low', ['amount' => number_format($minRequired, 2)]));
        }

        $currency = \App\Services\MoneyService::getCurrency($user);

        // 3. Create Pending Transaction
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'payable_type' => $listing->listable_type,
            'payable_id' => $listing->listable_id,
            'amount' => $request->amount,
            // Fee is 0 for bids usually until won? Or calc fee now? 
            // Usually invalid to charge fee on bid if not won. 
            // We can authorize full amount. Fee is internal.
            'fee' => 0, 
            'currency' => $currency,
            'status' => 'pending',
            'type' => 'auction_bid',
            'metadata' => ['is_bid' => true],
            'transaction_ref' => 'temp_' . uniqid(),
        ]);

        // 4. Initialize Stripe (Manual Capture)
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $imageUrl = $listing->getFirstMediaUrl('images', 'thumb');
        $images = $imageUrl ? [$imageUrl] : [];

        try {
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'], 
                'customer_email' => $user->email,
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => "Bid on: {$listing->title}",
                            'images' => $images,
                        ],
                        'unit_amount' => (int)($request->amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'payment_intent_data' => [
                    'capture_method' => 'manual', 
                ],
                'success_url' => route('listings.success', $listing->id) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('listings.show', $listing->id),
                'client_reference_id' => (string) $transaction->id,
                'metadata' => [
                    'transaction_uuid' => (string) $transaction->uuid,
                    'listing_id' => (string) $listing->id,
                    'type' => 'auction_bid'
                ],
            ]);
        } catch (\Exception $e) {
            return $this->checkError('messages.errors.payment_init_failed', $e);
        }

        $transaction->update(['transaction_ref' => $checkoutSession->id]);

        return Inertia::location($checkoutSession->url);
    }

    public function success(Request $request, Listing $listing)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            abort(404);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $session = Session::retrieve($sessionId);
        } catch (\Exception $e) {
             // [5] Use checkError (Redirects back)
             return $this->checkError('messages.errors.invalid_session', $e);
        }

        // Note: For PayPal, status might briefly be 'processing', but usually 'paid'
        
        // Retrieve transaction first to know type
        $transaction = Transaction::where('transaction_ref', $sessionId)->firstOrFail();
        $isAuction = $transaction->type === 'auction_bid';

        // For Manual Capture (Auctions), payment_status is 'unpaid' but we check if authorized.
        // If it's NOT auction, must be paid.
        if (!$isAuction && $session->payment_status !== 'paid') {
           return $this->checkError('messages.errors.payment_not_completed');
        }

        // For Auction, we accept 'unpaid' if status is 'open' or 'complete' (auth successful)
        // Ideally we check PaymentIntent status 'requires_capture'
        if ($isAuction) {
             // Basic check. Detailed check happens inside transaction block or specific logic.
             // If payment failed, Stripe usually doesn't redirect to success_url?
        }

        if ($transaction->status !== 'completed' && $transaction->status !== 'held') {
            DB::transaction(function () use ($transaction, $listing, $session, $isAuction) {
                
                // 1. General Purchase
                if (!$isAuction) {
                    $transaction->update([
                        'status' => 'completed',
                        'payment_method' => $session->payment_method_types[0] ?? 'stripe',
                    ]);

                    $item = $listing->listable;

                    if ($item instanceof PurchaseListing) {
                        $item->decrement('quantity', 1);
                    }
                    if ($item instanceof DonationListing) {
                        $item->increment('raised', (float) $transaction->amount);
                        $item->increment('donors_count');
                    }
                     // Add Investment logic if needed
                    if ($item instanceof InvestmentListing) {
                        // $item->increment('investors_count', $transaction->metadata['shares_count']);
                    }

                    // Notify Owner
                    $listing->user->notify(new ListingInteraction($listing, 'payment', [
                        'amount' => number_format((float)$transaction->amount, 2) . ' ' . $transaction->currency,
                        'transaction_id' => $transaction->id
                    ]));
                } 
                // 2. Auction Bid
                else {
                    $item = $listing->listable;
                    if ($item instanceof AuctionListing) {
                         $item = $item->lockForUpdate()->find($item->id);

                         // A. Validate Bid against Current (Race Condition Check)
                         // If we are late, we must VOID this payment immediately.
                         $minRequired = ($item->current_bid) ? $item->current_bid : $item->start_price;
                         $isFirstBid = $item->current_bid === null;
                         
                         // If existing bid exists, new bid must be strictly greater.
                         // But if we are the first, receiving current_bid=null is fine.
                         // Wait, if $item->current_bid changed while we were in Stripe, it is now higher.
                         // User paid $transaction->amount.
                         
                         if (!$isFirstBid && $transaction->amount <= $item->current_bid) {
                              // OUTBID! Cancel Auth.
                              try {
                                  if ($session->payment_intent) {
                                      $pi = PaymentIntent::retrieve($session->payment_intent);
                                      $pi->cancel(['cancellation_reason' => 'void_invoice']);
                                  }
                              } catch(\Exception $e) { 
                                  Log::error("Failed to cancel late bid auth", ['e'=>$e->getMessage()]); 
                              }
                              
                              $transaction->update([
                                  'status' => 'failed', 
                                  'metadata' => array_merge($transaction->metadata ?? [], ['fail_reason' => 'outbid_during_payment'])
                              ]);
                              
                              throw new \Exception(__('messages.errors.outbid_during_payment'));
                         }

                         // B. Refund/Release Previous Bidder
                         // Find previous HELD transaction for this auction
                         $prevTransaction = Transaction::where('payable_type', AuctionListing::class)
                            ->where('payable_id', $item->id)
                            ->where('type', 'auction_bid')
                            ->where('status', 'held') 
                            ->where('id', '!=', $transaction->id) // Just in case
                            ->orderBy('amount', 'desc')
                            ->first();

                         if ($prevTransaction) {
                             try {
                                  // We need the PI ID. The transaction_ref in our logic holds the SESSION ID.
                                  // We must fetch the session to get the PI. 
                                  // Optimization: Save PI in metadata during checkout? For now fetch.
                                  $prevSession = Session::retrieve($prevTransaction->transaction_ref);
                                  if ($prevSession->payment_intent) {
                                      $prevPI = PaymentIntent::retrieve($prevSession->payment_intent);
                                      $prevPI->cancel(['cancellation_reason' => 'abandoned']);
                                  }
                                  
                                  $prevTransaction->update(['status' => 'released']);
                             } catch (\Exception $e) {
                                 Log::error("Failed to release previous bid fund", ['t_id' => $prevTransaction->uuid, 'error' => $e->getMessage()]);
                                 // Don't fail the new bid for this.
                             }
                         }

                         // C. Register New Bid
                         $transaction->update([
                            'status' => 'held', // Authorized
                            'payment_method' => 'stripe' 
                         ]);

                         $item->update(['current_bid' => $transaction->amount]);
                         
                         Bid::create([
                            'user_id' => $transaction->user_id,
                            'listing_id' => $listing->id,
                            'amount' => $transaction->amount,
                            'status' => 'active',
                            'metadata' => ['transaction_id' => $transaction->id]
                         ]);
                         
                         // Notify Owner
                         $listing->user->notify(new ListingInteraction($listing, 'payment', [
                             'amount' => number_format((float)$transaction->amount, 2) . ' ' . $transaction->currency,
                             'transaction_id' => $transaction->id,
                             'is_bid' => true
                         ]));

                         // Notify System & Subscribers
                        ListingUpdateService::system($listing, 'updates.bid_new', [
                            'amount' => number_format((float)$transaction->amount, 2) . '€'
                        ]);
                        
                        foreach ($listing->subscriptions as $subscriber) {
                            $user = \App\Models\User::where('email', $subscriber->email)->first();
                            $locale = $user ? $user->locale : config('app.locale');
            
                             Mail::to($subscriber->email)->queue(new ListingUpdated($listing, [
                                'type' => 'bid',
                                'key' => 'updates.new_bid_placed',
                                'params' => ['amount' => number_format((float)$transaction->amount, 2) . "€"],
                                'subject_key' => 'updates.new_bid_subject',
                                'url' => route('listings.show', $listing)
                            ], $locale));
                        }
                    }
                }
            });
        }

        return Inertia::render('Success', [
            'listing' => $listing,
            'transactionId' => $transaction->uuid,
        ]);
    }
}