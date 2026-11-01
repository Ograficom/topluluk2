<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_settings_page_is_available(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard.profile'))
            ->assertOk()
            ->assertSee('Profil bilgileri');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'bio' => 'OgraFi kullanıcısı',
                'location' => 'Ankara',
                'company' => 'OgraFi',
                'education' => 'Üniversite',
                'profile_type' => 'person',
                'website_url' => 'https://example.com',
                'social_x' => 'https://x.com/example',
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('OgraFi kullanıcısı', $user->bio);
        $this->assertSame('Ankara', $user->location);
        $this->assertSame('OgraFi', $user->company);
        $this->assertSame('person', $user->profile_type);
    }
}
