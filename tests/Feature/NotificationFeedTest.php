<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_and_delete_are_scoped_to_the_signed_in_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $make = fn (User $owner, string $title) => $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test-notification',
            'data' => ['title' => $title, 'message' => 'Bildirim içeriği', 'category' => 'likes', 'url' => '/'],
        ]);
        $own = $make($user, 'Kendi bildirimim');
        $foreign = $make($other, 'Başkasının bildirimi');

        $this->actingAs($user)->getJson(route('notifications.feed'))
            ->assertOk()->assertJsonPath('unread', 1)
            ->assertSee('Kendi bildirimim')->assertDontSee('Başkasının bildirimi');

        $this->deleteJson(route('notifications.destroy', $foreign->id))->assertNotFound();
        $this->postJson(route('notifications.mark-read', $own->id))->assertOk();
        $this->getJson(route('notifications.feed'))->assertOk()->assertJsonPath('unread', 0);
        $this->deleteJson(route('notifications.destroy', $own->id))->assertOk();
        $this->getJson(route('notifications.feed'))->assertOk()->assertJsonPath('unread', 0)->assertDontSee('Kendi bildirimim');
        $this->assertDatabaseHas('notifications', ['id' => $foreign->id]);
    }
}
