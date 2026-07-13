<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('image_license_url', 2048)->nullable()->after('featured_image');
            $table->string('image_acquire_url', 2048)->nullable()->after('image_license_url');
            $table->string('image_credit_text', 255)->nullable()->after('image_acquire_url');
            $table->string('image_creator_name', 255)->nullable()->after('image_credit_text');
            $table->string('image_copyright_notice', 255)->nullable()->after('image_creator_name');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'image_license_url',
                'image_acquire_url',
                'image_credit_text',
                'image_creator_name',
                'image_copyright_notice',
            ]);
        });
    }
};
