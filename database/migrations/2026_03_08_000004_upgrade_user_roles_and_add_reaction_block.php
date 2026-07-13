<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyUser = 'user';
        $legacyBlocked = 'blocked';
        $legacySuperAdmin = 'super_admin';
        $admin = 'admin';
        $writer = 'writer';
        $banned = 'banned';

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('block_reactions')->default(false)->after('block_comments');
        });

        DB::table('users')
            ->where('role', $legacyUser)
            ->update(['role' => $writer]);

        DB::table('users')
            ->where('role', $legacyBlocked)
            ->update(['role' => $banned]);

        DB::table('users')
            ->where('role', $legacySuperAdmin)
            ->update(['role' => $admin]);

        DB::table('users')
            ->whereNull('role')
            ->orWhere('role', '')
            ->update(['role' => $writer]);

        if (!DB::table('users')->where('role', $admin)->exists()) {
            $firstUserId = DB::table('users')->orderBy('id')->value('id');

            if ($firstUserId) {
                DB::table('users')
                    ->where('id', $firstUserId)
                    ->update(['role' => $admin]);
            }
        }
    }

    public function down(): void
    {
        $legacyUser = 'user';
        $legacyBlocked = 'blocked';
        $writer = 'writer';
        $banned = 'banned';

        DB::table('users')
            ->where('role', $writer)
            ->update(['role' => $legacyUser]);

        DB::table('users')
            ->where('role', $banned)
            ->update(['role' => $legacyBlocked]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('block_reactions');
        });
    }
};
