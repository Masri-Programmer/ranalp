<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        // ------------------------------------------------
        // 1. GENERAL & BRANDING
        // ------------------------------------------------
        $this->migrator->add('general.site_name', 'Ranalp');
        $this->migrator->add('general.site_active', true);
        $this->migrator->add('general.debug_mode', false); // Useful for maintenance
        $this->migrator->add('general.logo_url', '/images/default-logo.png');
        $this->migrator->add('general.favicon_url', '/images/default-favicon.png');
        $this->migrator->add('general.contact_email', 'admin@ranalp.com');
        $this->migrator->add('general.support_phone', '+1 555-0123');

        // Pagination defaults
        $this->migrator->add('general.per_page', 15);
        $this->migrator->add('general.marketplace_per_page', 12); // Default for /listings
        $this->migrator->add('general.home_per_page', 8);        // Default for Home Page
        // ------------------------------------------------
        // 2. AUCTION LOGIC (Crucial for your app)
        // ------------------------------------------------
        // Anti-Sniping: If bid in last 60s, extend by 120s
        $this->migrator->add('auction.sniper_protection_seconds', 60);
        $this->migrator->add('auction.sniper_extension_seconds', 120);

        // Buffers: Time between auction end and winner processing
        $this->migrator->add('auction.processing_buffer_seconds', 300);

        // Fees: Global default fees (can be overridden per listing usually)
        $this->migrator->add('auction.default_commission_percent', 5.0);
        $this->migrator->add('auction.listing_fee', 0.00);

        // ------------------------------------------------
        // 3. CURRENCY & LOCALE
        // ------------------------------------------------
        $this->migrator->add('money.currency_code', 'EUR');
        $this->migrator->add('money.currency_symbol', '€');
        $this->migrator->add('money.symbol_position', 'right'); // 'left' or 'right'
        $this->migrator->add('money.decimal_separator', ',');
        $this->migrator->add('money.thousands_separator', '.');

        // ------------------------------------------------
        // 4. SEO & SOCIALS (Merged into General)
        // ------------------------------------------------
        $this->migrator->add('general.meta_title', 'Ranalp - Premium Auctions');
        $this->migrator->add('general.meta_description', 'The best place to buy and sell items.');
        $this->migrator->add('general.og_image', '/images/social-share.jpg');

        $this->migrator->add('general.facebook_url', 'https://facebook.com/ranalp');
        $this->migrator->add('general.twitter_url', 'https://twitter.com/ranalp');
        $this->migrator->add('general.instagram_url', null);

        // ------------------------------------------------
        // 5. BID INCREMENTS
        // ------------------------------------------------
        $this->migrator->add('auction.bid_increments', [
            ['from' => 0, 'increment' => 1],
            ['from' => 10, 'increment' => 2],
            ['from' => 50, 'increment' => 5],
            ['from' => 100, 'increment' => 10],
            ['from' => 500, 'increment' => 25],
            ['from' => 1000, 'increment' => 50],
        ]);
    }
};