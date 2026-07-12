<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run()
    {
        Role::firstOrCreate(['name' => 'administrator', 'guard_name' => 'web'], [
            'display_name' => 'Administrator',
        ]);

        Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web'], [
            'display_name' => 'Moderator',
        ]);

        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web'], [
            'display_name' => 'Editor',
        ]);

        Role::firstOrCreate(['name' => 'author', 'guard_name' => 'web'], [
            'display_name' => 'Author',
        ]);

        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web'], [
            'display_name' => 'User',
        ]);

        Role::firstOrCreate(['name' => 'readonly', 'guard_name' => 'web'], [
            'display_name' => 'User read-only',
        ]);
    }
}
