<?php

namespace App\Enums;

enum ListingType: string
{
    case PRIVATE_OCCASION = 'private_occasion';
    case CHARITY_ACTION = 'charity_action';
    case DONATION_CAMPAIGN = 'donation_campaign';
    case FOUNDERS_CREATIVES = 'founders_creatives';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
