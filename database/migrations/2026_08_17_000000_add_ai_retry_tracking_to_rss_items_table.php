<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rss_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('ai_rewrite_attempts')->default(0)->after('ai_rewrite_error');
            $table->timestamp('ai_last_attempted_at')->nullable()->after('ai_rewrite_attempts')->index();
        });
    }

    public function down(): void
    {
        Schema::table('rss_items', function (Blueprint $table) {
            $table->dropIndex(['ai_last_attempted_at']);
            $table->dropColumn(['ai_rewrite_attempts', 'ai_last_attempted_at']);
        });
    }
};
