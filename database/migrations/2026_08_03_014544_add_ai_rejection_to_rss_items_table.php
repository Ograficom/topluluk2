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
        Schema::table('rss_items', function (Blueprint $table) {
            $table->timestamp('ai_rejected_at')->nullable()->after('ai_rewrite_error');
            $table->string('ai_rejection_reason')->nullable()->after('ai_rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rss_items', function (Blueprint $table) {
            $table->dropColumn(['ai_rejected_at', 'ai_rejection_reason']);
        });
    }
};
