<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_user_with_a_profile_username(): void
    {
        $this->get('/signup')
            ->assertOk()
            ->assertSee('action="'.route('register.store').'"', false)
            ->assertSee('name="username"', false);

        $this->post('/signup', [
            'username' => 'aylin_yilmaz',
            'email' => 'aylin@example.com',
            'password' => 'a-strong-password-123',
            'password_confirmation' => 'a-strong-password-123',
            'agree_rules' => '1',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'name' => 'aylin_yilmaz',
            'username' => 'aylin_yilmaz',
            'email' => 'aylin@example.com',
        ]);
    }

    public function test_login_page_has_a_working_login_form(): void
    {
        $this->get('/signin')
            ->assertOk()
            ->assertSee('action="'.route('login.store').'"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="remember"', false);
    }

    public function test_registered_users_can_sign_in_with_their_username(): void
    {
        $user = User::factory()->create();

        $this->post('/signin', [
            'login_name' => $user->username,
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_settings_page_renders_the_original_settings_sections_for_a_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/setting.html')
            ->assertOk()
            ->assertSee('data-tabs-container="settings"', false)
            ->assertSee('data-tab-panel="profile"', false)
            ->assertSee('data-tab-panel="site"', false)
            ->assertSee('data-tab-panel="security"', false)
            ->assertSee('data-tab-panel="blacklist"', false)
            ->assertSee('data-tab-panel="verify"', false)
            ->assertSee('<button class="btn btn-red" type="submit">Delete account</button>', false)
            ->assertSee('sprite.svg#trash', false)
            ->assertSee('Request verification');
    }

    public function test_settings_page_handles_accounts_without_a_username(): void
    {
        $user = User::factory()->create(['username' => null]);

        $this->actingAs($user)
            ->get('/setting.html')
            ->assertOk()
            ->assertSee('Set a username in Profile settings');
    }

    public function test_migration_backfills_usernames_for_existing_accounts(): void
    {
        $user = User::factory()->create([
            'name' => 'Legacy Account',
            'email' => 'legacy-account@example.com',
            'username' => null,
        ]);

        $migration = require database_path('migrations/2026_11_01_015108_backfill_usernames.php');
        $migration->up();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'username' => 'legacy-account',
        ]);
    }

    public function test_profile_settings_update_saved_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/settings')
            ->patch('/settings/profile', [
                'activeTab' => 'profile',
                'username' => $user->username,
                'full_name' => 'Aylin Yılmaz',
                'signature' => 'Merhaba',
                'bio' => 'Profil açıklaması',
                'website' => 'https://example.com',
                'instagram' => 'aylin',
                'telegram' => '',
                'threads' => '',
                'youtube' => '',
            ])
            ->assertRedirect('/settings');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'full_name' => 'Aylin Yılmaz',
            'signature' => 'Merhaba',
            'bio' => 'Profil açıklaması',
            'website' => 'https://example.com',
        ]);
    }

    public function test_preferences_save_adult_content_and_timezone(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/settings')
            ->patch('/settings/preferences', [
                'activeTab' => 'site',
                'timezone' => 'Europe/Istanbul',
                'show_adult_content' => '1',
            ])
            ->assertRedirect('/settings');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'timezone' => 'Europe/Istanbul',
            'show_adult_content' => true,
            'notify_replies' => false,
            'notify_posts' => false,
            'notify_announcements' => false,
        ]);

        $this->get('/settings')
            ->assertOk()
            ->assertSee('id="settings-site-pane"', false)
            ->assertSee('class="tabs-panel is-active"', false);
    }

    public function test_email_change_requires_password_and_clears_verification(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($user)
            ->from('/settings')
            ->patch('/settings/email', [
                'activeTab' => 'security',
                'email' => 'new-address@example.com',
                'current_password' => 'password',
            ])
            ->assertRedirect('/settings');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new-address@example.com',
            'email_verified_at' => null,
        ]);
    }

    public function test_user_can_request_verification(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/settings')
            ->post('/settings/verification', [
                'activeTab' => 'verify',
                'verification_reason' => 'Bu hesabın bana ait olduğunu doğrulamak için başvuruyorum.',
            ])
            ->assertRedirect('/settings');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'verification_status' => 'pending',
            'verification_reason' => 'Bu hesabın bana ait olduğunu doğrulamak için başvuruyorum.',
        ]);
    }
}
