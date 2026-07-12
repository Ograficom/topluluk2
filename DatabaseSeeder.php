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
        if (env('DEMO_MODE') === false && app()->environment('production')) {
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
        }

        if (env('DEMO_MODE') === true && app()->environment('production')) {
            $this->call([
                SettingSeeder::class,
                RolesSeeder::class,
                PermissionsSeeder::class,
                LicenseKeySeeder::class,
                UserSeeder::class,
                UserSettingsSeeder::class,
                CommunitySeeder::class,
                PageSeeder::class,
                LevelSeeder::class,
                BadgeSeeder::class,
                AdSeeder::class,
            ]);
        }

        if (env('DEMO_MODE') === true && app()->environment('local')) {
            $this->call([
                SettingSeeder::class,
                RolesSeeder::class,
                PermissionsSeeder::class,
                LicenseKeySeeder::class,
                UserSeeder::class,
                UserSettingsSeeder::class,
                CommunitySeeder::class,
                PageSeeder::class,
                StorySeeder::class,
                TagSeeder::class,
                LevelSeeder::class,
                BadgeSeeder::class,
                AdSeeder::class,
            ]);
        }
    }
}
