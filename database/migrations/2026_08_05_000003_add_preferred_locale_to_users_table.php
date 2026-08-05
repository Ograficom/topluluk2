<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * routes/web.php's `locale.switch` route and the /dashboard/preferences update handler
 * already write to `$user->preferred_locale`, but the column never existed - every
 * logged-in user hitting either route would have hit a DB error. Adding it here so the
 * (now being wired up) language switcher actually works.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('preferred_locale', 10)->nullable()->after('profile_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferred_locale');
        });
    }
};
