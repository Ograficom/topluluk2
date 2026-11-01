<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where(function ($query): void {
                $query->whereNull('username')->orWhere('username', '');
            })
            ->orderBy('id')
            ->each(function (object $user): void {
                $baseUsername = Str::limit(
                    Str::slug(Str::before($user->email, '@')) ?: Str::slug($user->name) ?: 'user',
                    25,
                    '',
                );
                $username = $baseUsername;
                $suffix = 1;

                while (DB::table('users')->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                    $username = Str::limit($baseUsername, 25 - strlen((string) $suffix), '').'-'.$suffix++;
                }

                DB::table('users')->where('id', $user->id)->update(['username' => $username]);
            });
    }

    public function down(): void
    {
        //
    }
};
