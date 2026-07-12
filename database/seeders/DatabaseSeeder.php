<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            RolesSeeder::class,
            PermissionsSeeder::class,
            LicenseKeySeeder::class,
            PageSeeder::class,
            LevelSeeder::class,
            BadgeSeeder::class,
            AdSeeder::class,
        ]);

        if (config('alma.demo_mode') === true && app()->environment('production')) {
            $this->call([
                UserSeeder::class,
                UserSettingsSeeder::class,
                CommunitySeeder::class,
            ]);
        }

        if (config('alma.demo_mode') === true && app()->environment('local')) {
            $this->call([
                UserSeeder::class,
                UserSettingsSeeder::class,
                CommunitySeeder::class,
                StorySeeder::class,
                TagSeeder::class,
            ]);
        }
    }
}
