<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;
use Tests\TestCase;

class LocaleDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_locale_is_updated_on_login_based_on_location()
    {
        // Mock Location
        $position = new Position();
        $position->countryCode = 'ES';

        Location::shouldReceive('get')
            ->once()
            ->andReturn($position);

        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user);

        // Dispatch Login event manually as actingAs doesn't always dispatch it depending on setup, 
        // or we can hit the login endpoint. Hitting endpoint is better integration test.

        // Let's try simpler unit-ish test of the listener first/integrated via event dispatch
        $listener = new \App\Listeners\SetLanguageOnLogin(new \App\Services\LanguageService());
        $event = new Login('web', $user, false);

        $listener->handle($event);

        $this->assertEquals('es', $user->fresh()->locale);
        $this->assertEquals('es', session('locale'));
    }

    public function test_it_defaults_to_en_if_country_not_supported()
    {
        // Mock Location
        $position = new Position();
        $position->countryCode = 'CN'; // China, not in our map (assuming)

        Location::shouldReceive('get')
            ->once()
            ->andReturn($position);

        $user = User::factory()->create(['locale' => 'de']); // start with de

        $listener = new \App\Listeners\SetLanguageOnLogin(new \App\Services\LanguageService());
        $event = new Login('web', $user, false);

        $listener->handle($event);

        $this->assertEquals('en', $user->fresh()->locale); // Should default to en
        $this->assertEquals('en', session('locale'));
    }

    public function test_guest_locale_is_set_based_on_ip()
    {
        // Mock Location
        $position = new Position();
        $position->countryCode = 'ES';

        Location::shouldReceive('get')
            ->once()
            ->andReturn($position);

        $response = $this->get('/'); // Trigger middleware

        $this->assertEquals('es', session('locale'));
        $this->assertEquals('es', app()->getLocale());
    }
}
