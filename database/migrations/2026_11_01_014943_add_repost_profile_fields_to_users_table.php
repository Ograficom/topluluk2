<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->string('full_name')->nullable();
            $table->text('signature')->nullable();
            $table->text('bio')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('website', 2048)->nullable();
            $table->string('instagram')->nullable();
            $table->string('telegram')->nullable();
            $table->string('threads')->nullable();
            $table->string('youtube')->nullable();
            $table->string('timezone')->default('UTC');
            $table->boolean('show_adult_content')->default(false);
            $table->boolean('notify_replies')->default(true);
            $table->boolean('notify_posts')->default(true);
            $table->boolean('notify_announcements')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->string('verification_status')->default('unverified')->index();
            $table->text('verification_reason')->nullable();
            $table->timestamp('verification_requested_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'username',
                'full_name',
                'signature',
                'bio',
                'avatar_path',
                'website',
                'instagram',
                'telegram',
                'threads',
                'youtube',
                'timezone',
                'show_adult_content',
                'notify_replies',
                'notify_posts',
                'notify_announcements',
                'is_verified',
                'verification_status',
                'verification_reason',
                'verification_requested_at',
            ]);
        });
    }
};
