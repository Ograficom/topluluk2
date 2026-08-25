<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Testing\AiTestUserProfiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiTestUserProfilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_repeatable_ai_test_personas(): void
    {
        $users = AiTestUserProfiles::seed();

        $this->assertCount(6, $users);
        $this->assertSame(6, User::query()->where('is_ai_test_user', true)->count());
        $this->assertTrue($users->firstWhere('role', User::ROLE_ADMIN)->isAdmin());
        $this->assertTrue($users->firstWhere('ai_persona', 'banned')->isBanned());
        $this->assertTrue((bool) $users->firstWhere('ai_persona', 'restricted')->block_comments);

        AiTestUserProfiles::seed();

        $this->assertSame(6, User::query()->where('is_ai_test_user', true)->count());
    }

    public function test_the_artisan_command_is_available_in_testing(): void
    {
        $this->artisan('testing:seed-ai-users')
            ->assertSuccessful();

        $this->assertSame(6, User::query()->where('is_ai_test_user', true)->count());
    }
}
