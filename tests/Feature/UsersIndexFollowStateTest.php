<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersIndexFollowStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_button_toggles_and_users_index_reflects_it(): void
    {
        $viewer = User::factory()->create();
        $target = User::factory()->create(['username' => 'target-user']);

        $response = $this->actingAs($viewer)->post(route('users.follow', $target));
        $response->assertRedirect();
        $this->assertTrue($viewer->fresh()->followings()->where('followed_id', $target->id)->exists());

        $indexResponse = $this->actingAs($viewer)->get(route('users.index'));
        $indexResponse->assertSee('Takiptesin');

        // toggle again -> unfollow
        $this->actingAs($viewer)->post(route('users.follow', $target));
        $this->assertFalse($viewer->fresh()->followings()->where('followed_id', $target->id)->exists());
    }
}
