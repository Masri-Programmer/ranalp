<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ListingInteraction extends Notification
{
    use Queueable;

    public $listing;
    public $type;
    public $extraData;

    /**
     * Create a new notification instance.
     * 
     * @param Listing $listing
     * @param string $type ('payment', 'faq', 'review')
     * @param array $extraData
     */
    public function __construct(Listing $listing, string $type, array $extraData = [])
    {
        $this->listing = $listing;
        $this->type = $type;
        $this->extraData = $extraData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if (!$notifiable->wantsNotification($this->type)) {
            return [];
        }

        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $title = $this->getNotificationTitle();
        $description = $this->getNotificationDescription();

        return [
            'type' => $this->type,
            'listing_id' => $this->listing->id,
            'title' => $title,
            'description' => $description,
            'url' => route('listings.show', $this->listing->id),
            'extra_data' => $this->extraData,
        ];
    }

    protected function getNotificationTitle(): string
    {
        return match ($this->type) {
            'payment' => Lang::get('notifications.payment_title'),
            'faq' => Lang::get('notifications.faq_title'),
            'review' => Lang::get('notifications.review_title'),
            default => Lang::get('notifications.interaction_title'),
        };
    }

    protected function getNotificationDescription(): string
    {
        $listingTitle = $this->listing->title;

        return match ($this->type) {
            'payment' => Lang::get('notifications.payment_desc', [
                'listing' => $listingTitle,
                'amount' => $this->extraData['amount'] ?? ''
            ]),
            'faq' => Lang::get('notifications.faq_desc', [
                'listing' => $listingTitle
            ]),
            'review' => Lang::get('notifications.review_desc', [
                'listing' => $listingTitle,
                'rating' => $this->extraData['rating'] ?? ''
            ]),
            default => Lang::get('notifications.interaction_desc', ['listing' => $listingTitle]),
        };
    }
}
