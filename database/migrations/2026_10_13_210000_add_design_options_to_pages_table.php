<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('layout_width', 20)->default('contained')->after('blocks');
            $table->string('content_background', 32)->nullable()->after('layout_width');
            $table->string('content_text_color', 32)->nullable()->after('content_background');
            $table->unsignedSmallInteger('content_padding')->default(24)->after('content_text_color');
            $table->string('content_radius', 20)->default('rounded')->after('content_padding');
            $table->longText('custom_css')->nullable()->after('content_radius');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'layout_width',
                'content_background',
                'content_text_color',
                'content_padding',
                'content_radius',
                'custom_css',
            ]);
        });
    }
};
