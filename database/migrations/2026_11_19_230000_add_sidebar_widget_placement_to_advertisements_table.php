<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table): void {
            $table->boolean('show_in_sidebar_widget')->default(true)->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table): void {
            $table->dropIndex(['show_in_sidebar_widget']);
            $table->dropColumn('show_in_sidebar_widget');
        });
    }
};
