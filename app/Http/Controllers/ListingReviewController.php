<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListingReviewResource;
use App\Models\Listing;
use App\Models\ListingReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ListingReviewController extends Controller
{
    /**
     * Display a listing of reviews.
     */
    public function index(Request $request, Listing $listing)
    {
        // 1. Fetch Reviews
        $reviews = $listing->reviews()
            ->with('user')
            ->orderByDesc('created_at')
            ->simplePaginate(5);

        return Inertia::render('listings/Show', [
            'listing' => $listing,

            'reviews' => ListingReviewResource::collection($reviews),
        ]);
    }

    /**
     * Store a new review.
     */
    public function store(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body' => 'required|string|min:10',
        ]);

        // 1. Prevent owner from reviewing their own listing
        if ($listing->user_id === Auth::id()) {
            return $this->checkError('messages.errors.own_review');
        }

        // 2. Check if user already reviewed
        $existing = $listing->reviews()->where('user_id', Auth::id())->exists();
        if ($existing) {
            return $this->checkError('reviews.already_reviewed');
        }

        $review = $listing->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'body' => $validated['body'],
        ]);

        return $this->checkSuccess($review, 'created');
    }

    /**
     * Update an existing review.
     */
    public function update(Request $request, ListingReview $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body' => 'required|string|min:10',
        ]);

        $review->update($validated);

        return $this->checkSuccess($review, 'updated');
    }

    /**
     * Remove a review.
     */
    public function destroy(ListingReview $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $review->delete();

        return $this->checkSuccess($review, 'deleted');
    }
}
