<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('FILAMENT_ADMIN_EMAIL');
        $password = env('FILAMENT_ADMIN_PASSWORD');

        if (filled($email) xor filled($password)) {
            throw new InvalidArgumentException(
                'Set both FILAMENT_ADMIN_EMAIL and FILAMENT_ADMIN_PASSWORD to seed the administrator.',
            );
        }

        if (filled($email) && filled($password)) {
            $admin = User::query()->firstOrNew(['email' => $email]);
            $admin->fill([
                'name' => env('FILAMENT_ADMIN_NAME', 'Repost Admin'),
                'password' => $password,
                'is_admin' => true,
            ]);

            if (blank($admin->username)) {
                $baseUsername = Str::limit(Str::slug(Str::before($email, '@')) ?: 'admin', 25, '');
                $username = $baseUsername;
                $suffix = 1;

                while (User::query()->where('username', $username)->exists()) {
                    $username = Str::limit($baseUsername, 25 - strlen((string) $suffix), '').'-'.$suffix++;
                }

                $admin->username = $username;
            }

            $admin->save();
        }

        $this->call(DemoContentSeeder::class);
    }
}
