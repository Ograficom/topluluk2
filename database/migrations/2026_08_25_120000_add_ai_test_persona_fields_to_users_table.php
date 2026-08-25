<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_ai_test_user')->default(false)->index()->after('profile_type');
            $table->string('ai_persona', 80)->nullable()->after('is_ai_test_user');
            $table->text('ai_system_prompt')->nullable()->after('ai_persona');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['is_ai_test_user']);
            $table->dropColumn(['is_ai_test_user', 'ai_persona', 'ai_system_prompt']);
        });
    }
};
