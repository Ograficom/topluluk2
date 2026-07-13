<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('social_login_settings')) {
            return;
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('social_login_settings', 'facebook_enabled') ? 'facebook_enabled' : null,
            Schema::hasColumn('social_login_settings', 'facebook_client_id') ? 'facebook_client_id' : null,
            Schema::hasColumn('social_login_settings', 'facebook_client_secret') ? 'facebook_client_secret' : null,
            Schema::hasColumn('social_login_settings', 'facebook_redirect_url') ? 'facebook_redirect_url' : null,
        ]));

        if ($columns === []) {
            return;
        }

        Schema::table('social_login_settings', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('social_login_settings')) {
            return;
        }

        Schema::table('social_login_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('social_login_settings', 'facebook_enabled')) {
                $table->boolean('facebook_enabled')->default(false)->after('google_redirect_url');
            }

            if (!Schema::hasColumn('social_login_settings', 'facebook_client_id')) {
                $table->string('facebook_client_id')->nullable()->after('facebook_enabled');
            }

            if (!Schema::hasColumn('social_login_settings', 'facebook_client_secret')) {
                $table->string('facebook_client_secret')->nullable()->after('facebook_client_id');
            }

            if (!Schema::hasColumn('social_login_settings', 'facebook_redirect_url')) {
                $table->string('facebook_redirect_url')->nullable()->after('facebook_client_secret');
            }
        });
    }
};
