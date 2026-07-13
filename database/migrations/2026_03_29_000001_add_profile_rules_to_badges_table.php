<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('badges')) {
            Schema::table('badges', function (Blueprint $table): void {
                if (!Schema::hasColumn('badges', 'eligible_profile_type')) {
                    $table->string('eligible_profile_type', 32)->nullable()->after('icon_svg_path');
                }

                if (!Schema::hasColumn('badges', 'requires_verified')) {
                    $table->boolean('requires_verified')->default(false)->after('eligible_profile_type');
                }
            });

            DB::table('badges')
                ->select(['id', 'name', 'slug', 'min_points'])
                ->orderBy('id')
                ->get()
                ->each(function (object $badge): void {
                    $name = strtolower(trim((string) ($badge->name ?? '')));
                    $slug = strtolower(trim((string) ($badge->slug ?? '')));

                    if ($name !== 'business' && $slug !== 'business') {
                        return;
                    }

                    DB::table('badges')
                        ->where('id', $badge->id)
                        ->update([
                            'eligible_profile_type' => 'organization',
                            'requires_verified' => true,
                            'min_points' => 0,
                            'updated_at' => now(),
                        ]);

                    if (!Schema::hasTable('badge_user')) {
                        return;
                    }

                    DB::table('users')
                        ->where('profile_type', 'organization')
                        ->where('is_verified', true)
                        ->select(['id', 'badge_points'])
                        ->orderBy('id')
                        ->get()
                        ->each(function (object $user) use ($badge): void {
                            DB::table('badge_user')->updateOrInsert(
                                [
                                    'badge_id' => $badge->id,
                                    'user_id' => $user->id,
                                ],
                                [
                                    'awarded_points' => max(0, (int) ($user->badge_points ?? 0)),
                                    'awarded_at' => now(),
                                    'updated_at' => now(),
                                    'created_at' => now(),
                                ],
                            );
                        });
                });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('badges')) {
            return;
        }

        Schema::table('badges', function (Blueprint $table): void {
            if (Schema::hasColumn('badges', 'requires_verified')) {
                $table->dropColumn('requires_verified');
            }

            if (Schema::hasColumn('badges', 'eligible_profile_type')) {
                $table->dropColumn('eligible_profile_type');
            }
        });
    }
};
