<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('deleted_by_sender')->default(false)->after('attachment_size');
            $table->boolean('deleted_by_recipient')->default(false)->after('deleted_by_sender');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['deleted_by_sender', 'deleted_by_recipient']);
        });
    }
};
