<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'block_reaction_uploads')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->boolean('block_reaction_uploads')
                    ->default(false)
                    ->after('block_reactions');
            });
        }

        if (Schema::hasTable('reaction_types') && ! Schema::hasColumn('reaction_types', 'submitted_by_user_id')) {
            Schema::table('reaction_types', function (Blueprint $table): void {
                $table->foreignId('submitted_by_user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reaction_types') && Schema::hasColumn('reaction_types', 'submitted_by_user_id')) {
            Schema::table('reaction_types', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('submitted_by_user_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'block_reaction_uploads')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('block_reaction_uploads');
            });
        }
    }
};
