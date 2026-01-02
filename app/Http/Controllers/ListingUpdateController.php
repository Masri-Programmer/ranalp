<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Resources\ListingUpdateResource;

class ListingUpdateController extends Controller
{
    /**
     * Display a listing of updates.
     */
    public function index(Request $request, Listing $listing)
    {
        $updates = $listing->updates()
            ->orderByDesc('created_at')
            ->paginate(5); // Consistent with reviews

        $resource = ListingUpdateResource::collection($updates);

        if ($request->header('X-Inertia')) {
            return Inertia::render('listings/Show', [
                'listing' => array_merge($listing->toArray(), [
                    'updates' => $resource,
                    'next_update_page_url' => $updates->nextPageUrl(),
                ]),
            ]);
        }

        return response()->json([
            'updates' => $resource,
            'next_update_page_url' => $updates->nextPageUrl(),
        ]);
    }

    /**
     * Store a new update.
     */
    public function store(Request $request, Listing $listing)
    {
        if ($listing->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $update = $listing->updates()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => 'manual',
        ]);

        return $this->checkSuccess($update, 'created');
    }

    /**
     * Update an existing update.
     */
    public function update(Request $request, ListingUpdate $update)
    {
        if ($update->listing->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $update->update($validated);

        return $this->checkSuccess($update, 'updated');
    }

    /**
     * Remove an update.
     */
    public function destroy(ListingUpdate $update)
    {
        if ($update->listing->user_id !== Auth::id()) {
            abort(403);
        }

        $update->delete();

        return $this->checkSuccess($update, 'deleted');
    }
}