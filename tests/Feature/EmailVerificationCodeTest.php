<?php

namespace Tests\Feature;

use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\EmailVerificationCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_receives_a_six_digit_verification_code(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        $this->assertDatabaseHas('email_verification_codes', [
            'user_id' => $user->id,
            'attempts' => 0,
        ]);

        Notification::assertSentTo($user, EmailVerificationCodeNotification::class, function ($notification): bool {
            return preg_match('/^\d{6}$/', $notification->code) === 1;
        });
    }

    public function test_valid_code_verifies_email_and_removes_code(): void
    {
        $user = User::factory()->unverified()->create();
        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('verification.code.verify'), ['code' => '123456'])
            ->assertRedirect();

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->id]);
    }

    public function test_invalid_code_increments_attempt_count(): void
    {
        $user = User::factory()->unverified()->create();
        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'sent_at' => now(),
        ]);

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.code.verify'), ['code' => '654321'])
            ->assertSessionHasErrors('code');

        $this->assertDatabaseHas('email_verification_codes', [
            'user_id' => $user->id,
            'attempts' => 1,
        ]);
    }
}
