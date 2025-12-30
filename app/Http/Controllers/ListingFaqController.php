<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingFaq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ListingInteraction;

class ListingFaqController extends Controller
{
    // User asks a question
    public function store(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $faq = new ListingFaq();
        $faq->listing_id = $listing->id;
        $faq->user_id = Auth::id();

        $faq->is_visible = (Auth::id() === $listing->user_id);

        $faq->setTranslation('question', app()->getLocale(), $validated['question']);
        $faq->save();

        // Notify Listing Owner if the asker is NOT the owner
        if (Auth::id() !== $listing->user_id) {
            $listing->user->notify(new ListingInteraction($listing, 'faq', [
                'faq_id' => $faq->id,
                'question' => $validated['question']
            ]));
        }

        return $this->checkSuccess(ListingFaq::class,);
    }

    public function update(Request $request, Listing $listing, ListingFaq $faq)
    {
        $user = Auth::user();
        
        if ($user->id !== $listing->user_id && $user->id !== $faq->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'question' => 'nullable|string|max:500',
            'answer' => 'nullable|string',
            'is_visible' => 'boolean'
        ]);

        if ($user->id === $faq->user_id && $user->id !== $listing->user_id) {
            if ($request->has('question')) {
                $faq->setTranslation('question', app()->getLocale(), $validated['question']);
            }
        }

        if ($user->id === $listing->user_id) {
            if ($request->has('answer')) {
                $faq->setTranslation('answer', app()->getLocale(), $validated['answer']);
                if (!$faq->is_visible) {
                    $faq->is_visible = true;
                }
            }
            
            if ($request->has('is_visible')) {
                $faq->is_visible = $validated['is_visible'];
            }
        }

        $faq->save();

        return $this->checkSuccess($faq, );
    }

    public function destroy(Listing $listing, ListingFaq $faq)
    {
        $user = Auth::user();
        
        if ($user->id !== $listing->user_id && $user->id !== $faq->user_id) {
            abort(403);
        }

        $faq->delete();
        
        return $this->checkSuccess($faq, );
    }
}