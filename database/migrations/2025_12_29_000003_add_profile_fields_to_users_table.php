<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('profile_photo_path');
            $table->string('social_x', 255)->nullable()->after('bio');
            $table->string('social_instagram', 255)->nullable()->after('social_x');
            $table->string('social_whatsapp', 255)->nullable()->after('social_instagram');
            $table->string('social_tiktok', 255)->nullable()->after('social_whatsapp');
            $table->string('social_facebook', 255)->nullable()->after('social_tiktok');
            $table->string('website_url', 255)->nullable()->after('social_facebook');
            $table->timestamp('joined_at')->nullable()->after('website_url');
            $table->boolean('is_verified')->default(false)->after('joined_at');
            $table->string('verification_badge', 100)->nullable()->after('is_verified');
            $table->text('verification_badge_svg')->nullable()->after('verification_badge');
        });

        DB::table('users')
            ->whereNull('joined_at')
            ->update(['joined_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'social_x',
                'social_instagram',
                'social_whatsapp',
                'social_tiktok',
                'social_facebook',
                'website_url',
                'joined_at',
                'is_verified',
                'verification_badge',
                'verification_badge_svg',
            ]);
        });
    }
};
