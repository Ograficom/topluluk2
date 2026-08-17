<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DailyDigestSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_daily_digest_with_verified_account_email(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('dashboard.notifications.update'), [
            'comments' => '1',
            'daily_digest_enabled' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'notifications-updated');

        $user->refresh();

        $this->assertTrue($user->daily_digest_enabled);
        $this->assertSame(mb_strtolower($user->email), $user->daily_digest_email);
        $this->assertNotNull($user->daily_digest_email_verified_at);
    }

    public function test_changing_to_account_email_marks_digest_address_as_verified(): void
    {
        $user = User::factory()->create([
            'daily_digest_email' => 'old@example.com',
            'daily_digest_email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->put(
            route('dashboard.notifications.digest-email'),
            ['daily_digest_email' => strtoupper($user->email)]
        );

        $response->assertRedirect();
        $response->assertSessionHas('status', 'digest-email-updated');

        $user->refresh();

        $this->assertSame(mb_strtolower($user->email), $user->daily_digest_email);
        $this->assertNotNull($user->daily_digest_email_verified_at);
    }

    public function test_signed_link_verifies_current_digest_address(): void
    {
        $user = User::factory()->create([
            'daily_digest_email' => 'digest@example.com',
            'daily_digest_email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'digest.email.verify',
            now()->addHour(),
            [
                'userId' => $user->id,
                'hash' => sha1('digest@example.com'),
            ],
        );

        $this->get($url)->assertRedirect(route('login'));

        $this->assertNotNull($user->fresh()->daily_digest_email_verified_at);
    }

    public function test_signed_unsubscribe_link_disables_daily_digest(): void
    {
        $user = User::factory()->create([
            'daily_digest_enabled' => true,
            'daily_digest_email' => 'digest@example.com',
            'daily_digest_email_verified_at' => now(),
        ]);

        $url = URL::signedRoute('digest.unsubscribe', [
            'userId' => $user->id,
            'hash' => sha1('digest@example.com'),
        ]);

        $this->get($url)->assertRedirect(route('login'));

        $this->assertFalse($user->fresh()->daily_digest_enabled);
    }
}
