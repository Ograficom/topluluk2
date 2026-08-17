<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('daily_digest_enabled')->default(false)->after('comments_visibility');
            $table->string('daily_digest_email')->nullable()->after('daily_digest_enabled');
            $table->timestamp('daily_digest_email_verified_at')->nullable()->after('daily_digest_email');
            $table->timestamp('daily_digest_last_sent_at')->nullable()->after('daily_digest_email_verified_at');

            $table->index(
                ['daily_digest_enabled', 'daily_digest_email_verified_at'],
                'users_daily_digest_eligible_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_daily_digest_eligible_index');
            $table->dropColumn([
                'daily_digest_enabled',
                'daily_digest_email',
                'daily_digest_email_verified_at',
                'daily_digest_last_sent_at',
            ]);
        });
    }
};
