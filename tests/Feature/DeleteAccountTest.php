<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_accounts_can_be_deleted_through_the_application_endpoint(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('dashboard.delete-account.destroy'), ['password' => 'password'])
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertGuest('web');
    }

    public function test_account_deletion_requires_the_correct_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('dashboard.delete-account.destroy'), ['password' => 'wrong-password'])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
