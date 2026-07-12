<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::withTrashed()->each(function ($user) {
            $user->update([
                'preference_settings' => [
                    'show_nsfw' => true,
                    'blur_nsfw' => true,
                    'open_posts_new_tab' => false,
                ],
                'notify_settings' => [
                    'new_comments' => true,
                    'replies_comments' => true,
                    'liked' => true,
                    'new_follower' => true,
                    'mentions' => true,
                ],
            ]);
        });
    }
}
