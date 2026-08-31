<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reaction_types')) {
            return;
        }

        if (! Schema::hasColumn('reaction_types', 'submitted_by_user_id')) {
            Schema::table('reaction_types', function (Blueprint $table): void {
                $table->foreignId('submitted_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('reaction_types', 'moderation_status')) {
            Schema::table('reaction_types', function (Blueprint $table): void {
                $table->string('moderation_status', 20)
                    ->default('approved')
                    ->index();
            });
        }

        if (! Schema::hasColumn('reaction_types', 'moderation_note')) {
            Schema::table('reaction_types', function (Blueprint $table): void {
                $table->text('moderation_note')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('reaction_types')) {
            return;
        }

        if (Schema::hasColumn('reaction_types', 'submitted_by_user_id')) {
            Schema::table('reaction_types', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('submitted_by_user_id');
            });
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('reaction_types', 'moderation_status') ? 'moderation_status' : null,
            Schema::hasColumn('reaction_types', 'moderation_note') ? 'moderation_note' : null,
        ]));

        if ($columns !== []) {
            Schema::table('reaction_types', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
