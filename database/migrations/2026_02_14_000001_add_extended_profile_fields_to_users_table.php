<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'location')) {
                $table->string('location', 255)->nullable()->after('bio');
            }

            if (!Schema::hasColumn('users', 'company')) {
                $table->string('company', 255)->nullable()->after('location');
            }

            if (!Schema::hasColumn('users', 'education')) {
                $table->string('education', 255)->nullable()->after('company');
            }

            if (!Schema::hasColumn('users', 'social_youtube')) {
                $table->string('social_youtube', 255)->nullable()->after('social_facebook');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $drops = [];

            foreach (['location', 'company', 'education', 'social_youtube'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $drops[] = $column;
                }
            }

            if ($drops !== []) {
                $table->dropColumn($drops);
            }
        });
    }
};
