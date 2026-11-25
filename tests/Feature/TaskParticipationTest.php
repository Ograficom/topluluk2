<?php

namespace Tests\Feature;

use App\Models\ReputationSetting;
use App\Models\TaskParticipation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskParticipationTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_task_submission_is_recorded_once_without_awarding_points(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'is_company' => false]);
        ReputationSetting::current()->update(['enabled' => true, 'tasks' => [[
            'key' => 'instagram-test', 'title' => 'Instagram takip', 'activity' => 'social_follow',
            'target_count' => 1, 'reward_points' => 10, 'active' => true,
            'starts_at' => now()->subHour()->toDateTimeString(), 'approved_users' => [],
        ]]]);
        $url = route('tasks.submit', ['taskKey' => 'instagram-test']);
        $this->post($url, ['external_username' => '@tester'])->assertRedirect(route('login'));
        $this->actingAs($user)->post($url, ['external_username' => '@tester'])->assertRedirect();
        $this->actingAs($user)->post($url, ['external_username' => '@other'])->assertRedirect();
        $this->assertSame(1, TaskParticipation::query()->where('user_id', $user->id)->count());
        $this->assertDatabaseHas('task_participations', ['user_id' => $user->id, 'task_key' => 'instagram-test', 'status' => 'pending', 'external_username' => '@tester']);
        $this->assertDatabaseMissing('task_completions', ['user_id' => $user->id, 'task_key' => 'instagram-test']);
    }
}
