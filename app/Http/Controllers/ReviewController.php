<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Notifications\ListingInteraction;

class ReviewController extends Controller
{
    /**
     * Get paginated reviews for a listing.
     */
    public function index(Request $request, Listing $listing)
    {
        $reviews = $listing->reviews()
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'user' => [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                        'profile_photo_url' => $review->user->profile_photo_url ?? null,
                    ],
                    'rating' => $review->rating,
                    'body' => $review->body,
                    'created_at' => $review->created_at,
                    'time_ago' => $review->created_at->diffForHumans(),
                    'can_edit' => Auth::id() === $review->user_id,
                ];
            }),
            'next_page_url' => $reviews->nextPageUrl(),
        ]);
    }


    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body' => 'required|string|min:3|max:1000',
        ]);

        // Optional: Prevent user from reviewing their own listing
        if ($listing->user_id === Auth::id()) {
            return $this->checkError('messages.errors.own_review');
        }

        // Optional: Check if user already reviewed this listing
        $existingReview = $listing->reviews()->where('user_id', Auth::id())->first();
        if ($existingReview) {
            return $this->checkError('messages.errors.already_reviewed');
        }

        $review = $listing->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'body' => $validated['body'],
        ]);

        // Notify Listing Owner
        $listing->user->notify(new ListingInteraction($listing, 'review', [
            'review_id' => $review->id,
            'rating' => $validated['rating']
        ]));

        return $this->checkSuccess(Review::class, );
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, Review $review)
    {
        // Ensure the authenticated user owns the review
        if ($review->user_id !== Auth::id()) {
            abort(403, __('messages.errors.unauthorized'));
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body' => 'required|string|min:3|max:1000',
        ]);

        $review->update($validated);

        return $this->checkSuccess($review, );
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review)
    {
        // Ensure the authenticated user owns the review or is an admin
        if ($review->user_id !== Auth::id()) {
            abort(403, __('messages.errors.unauthorized'));
        }


        $review->delete();

        return $this->checkSuccess($review, );
    }
}