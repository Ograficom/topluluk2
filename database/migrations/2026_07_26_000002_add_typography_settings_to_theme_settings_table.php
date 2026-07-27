<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            foreach (['heading', 'body', 'button', 'nav', 'code'] as $role) {
                $table->text("font_{$role}_file")->nullable();
                $table->text("font_{$role}_fallback")->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            foreach (['heading', 'body', 'button', 'nav', 'code'] as $role) {
                $table->dropColumn(["font_{$role}_file", "font_{$role}_fallback"]);
            }
        });
    }
};
