<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_custom_registration_screen_remains_available_when_fortify_registration_is_disabled(): void
    {
        if (Features::enabled(Features::registration())) {
            $this->markTestSkipped('Fortify registration is enabled.');
        }

        // The application intentionally uses a custom email-code registration
        // flow, so disabling Fortify's native registration feature must not
        // hide the custom /register entry point.
        $this->get('/register')->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        if (! Features::enabled(Features::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_sends_email_verification_notification(): void
    {
        if (! Features::enabled(Features::registration()) || ! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Registration or email verification support is not enabled.');
        }

        Notification::fake();

        $this->post('/register', [
            'name' => 'Mail User',
            'email' => 'mail-user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $user = User::where('email', 'mail-user@example.com')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
