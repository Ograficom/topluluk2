<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('featured_image', 2048)->nullable()->after('excerpt');
            $table->longText('content_json')->nullable()->after('content');
            $table->string('meta_title', 255)->nullable()->after('slug');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('meta_keywords')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'featured_image',
                'content_json',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ]);
        });
    }
};
