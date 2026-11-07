<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_pages', function (Blueprint $table): void {
            $table->unsignedInteger('footer_sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('site_pages', function (Blueprint $table): void {
            $table->dropColumn('footer_sort_order');
        });
    }
};
