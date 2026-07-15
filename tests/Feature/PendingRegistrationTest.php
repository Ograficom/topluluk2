<?php

namespace Tests\Feature;

use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\PendingRegistrationCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PendingRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_registration_endpoint_cannot_bypass_email_verification(): void
    {
        $this->post('/register', [
            'name' => 'Atlayan Kullanıcı',
            'email' => 'bypass@ografi.com',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ])->assertStatus(405);

        $this->assertDatabaseMissing('users', ['email' => 'bypass@ografi.com']);
    }

    public function test_user_is_not_created_before_email_code_is_verified(): void
    {
        Notification::fake();

        $this->post(route('register.email'), ['email' => 'new@ografi.com'])
            ->assertRedirect(route('register.verify'));

        $this->assertDatabaseMissing('users', ['email' => 'new@ografi.com']);
        $this->assertDatabaseHas('pending_registrations', ['email' => 'new@ografi.com']);
        Notification::assertSentOnDemand(PendingRegistrationCodeNotification::class);
    }

    public function test_verified_pending_email_can_complete_registration(): void
    {
        $pending = PendingRegistration::create([
            'token' => str_repeat('a', 64),
            'email' => 'verified@ografi.com',
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'sent_at' => now(),
        ]);

        $this->withSession(['pending_registration_token' => $pending->token])
            ->post(route('register.verify.submit'), ['code' => '123456'])
            ->assertRedirect(route('register.complete'));

        $this->withSession(['pending_registration_token' => $pending->token])
            ->post(route('register.complete.submit'), [
                'name' => 'Yeni Üye',
                'password' => 'StrongPassword123!',
                'password_confirmation' => 'StrongPassword123!',
            ])
            ->assertRedirect(route('home'));

        $user = User::where('email', 'verified@ografi.com')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('pending_registrations', ['email' => 'verified@ografi.com']);
    }
}
