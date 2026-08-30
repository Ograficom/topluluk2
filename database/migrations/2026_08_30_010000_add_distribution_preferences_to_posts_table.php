<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('followers_only')->default(false)->index()->after('noindex');
            $table->boolean('is_ai_product')->default(false)->after('followers_only');
            $table->boolean('hide_from_feeds')->default(false)->index()->after('is_ai_product');
            $table->boolean('suppress_follower_notifications')->default(false)->after('hide_from_feeds');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['followers_only']);
            $table->dropIndex(['hide_from_feeds']);
            $table->dropColumn([
                'followers_only',
                'is_ai_product',
                'hide_from_feeds',
                'suppress_follower_notifications',
            ]);
        });
    }
};
