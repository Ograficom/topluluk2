<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reputation_settings', function (Blueprint $table): void {
            $table->json('community_tasks')->nullable()->after('reward_badges');
        });
    }

    public function down(): void
    {
        Schema::table('reputation_settings', function (Blueprint $table): void {
            $table->dropColumn('community_tasks');
        });
    }
};
