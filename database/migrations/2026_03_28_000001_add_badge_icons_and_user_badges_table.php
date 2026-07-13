<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('badges', 'icon_svg_path')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->string('icon_svg_path', 2048)->nullable()->after('icon');
            });
        }

        if (!Schema::hasTable('badge_user')) {
            Schema::create('badge_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('awarded_points')->default(0);
                $table->timestamp('awarded_at')->nullable();
                $table->timestamps();
                $table->unique(['badge_id', 'user_id']);
            });

            $badges = DB::table('badges')
                ->where('is_active', true)
                ->get(['id', 'min_points']);

            $users = DB::table('users')
                ->where('badge_points', '>', 0)
                ->get(['id', 'badge_points']);

            $now = now();
            $rows = [];

            foreach ($users as $user) {
                foreach ($badges as $badge) {
                    if ((int) $user->badge_points < (int) $badge->min_points) {
                        continue;
                    }

                    $rows[] = [
                        'badge_id' => $badge->id,
                        'user_id' => $user->id,
                        'awarded_points' => (int) $user->badge_points,
                        'awarded_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (count($rows) >= 500) {
                        DB::table('badge_user')->insertOrIgnore($rows);
                        $rows = [];
                    }
                }
            }

            if ($rows !== []) {
                DB::table('badge_user')->insertOrIgnore($rows);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('badge_user')) {
            Schema::drop('badge_user');
        }

        if (Schema::hasColumn('badges', 'icon_svg_path')) {
            Schema::table('badges', function (Blueprint $table) {
                $table->dropColumn('icon_svg_path');
            });
        }
    }
};
