<?php

namespace App\Http\Controllers;

use App\Traits\HasAppMessages; // Import the trait
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    use HasAppMessages; // Enable the trait

    /**
     * Display a listing of the notifications.
     */
    public function index()
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(10);

        return Inertia::render('notifications/Index', [
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        // Uses the trait to return success with 'deleted' action
        return $this->checkSuccess('Benachrichtigung', 'deleted');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        // Custom success message using the trait
        return $this->checkSuccess('Benachrichtigungen', 'updated');
    }

    /**
     * Update notification preferences.
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $user->notification_settings = $request->validate([
            'new_bid' => 'boolean',
            'item_sold' => 'boolean',
            'new_review' => 'boolean',
        ]);
        $user->save();
        
        return $this->checkSuccess('Einstellungen', 'updated');
    }

    /**
     * Mark a single notification as read and redirect.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if (isset($notification->data['url'])) {
             return Inertia::location($notification->data['url']);
        }
        
        // If no URL, standard success feedback
        return $this->checkSuccess('Benachrichtigung', 'updated'); 
    }
}