<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class ReleaseBidFunds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Transaction $transaction) {}

    public function handle(): void
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Retrieve Session to find the PaymentIntent
            $session = Session::retrieve($this->transaction->transaction_ref);
            
            if ($session->payment_intent) {
                $pi = PaymentIntent::retrieve($session->payment_intent);
                // Cancel the hold (release funds)
                $pi->cancel(['cancellation_reason' => 'abandoned']);
            }

            $this->transaction->update(['status' => 'released']);
            
        } catch (\Exception $e) {
            Log::error("Failed to release bid funds", [
                'transaction_id' => $this->transaction->uuid,
                'error' => $e->getMessage()
            ]);
            // Optional: Release back to queue or alert admin
        }
    }
}