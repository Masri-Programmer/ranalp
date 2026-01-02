<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    // Site Identity
    public string $site_name;
    public bool $site_active;
    public bool $debug_mode;
    public ?string $logo_url;
    public ?string $favicon_url;

    // Contact
    public string $contact_email;
    public string $support_phone;

    // System
    public int $per_page;
    public int $marketplace_per_page;
    public int $home_per_page;
    // SEO & Socials
    public string $meta_title;
    public string $meta_description;
    public ?string $og_image;

    public ?string $facebook_url;
    public ?string $twitter_url;
    public ?string $instagram_url;

    public static function group(): string
    {
        return 'general';
    }
}
