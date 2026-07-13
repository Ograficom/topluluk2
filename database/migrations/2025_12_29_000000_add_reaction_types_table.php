<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reaction_types')) {
            Schema::create('reaction_types', function (Blueprint $table) {
                $table->id();
                $table->string('label');
                $table->string('short_code')->unique();
                $table->string('emoji')->nullable();
                $table->string('gif_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('reactions', function (Blueprint $table) {
            if (! Schema::hasColumn('reactions', 'reaction_type_id')) {
                $table->foreignId('reaction_type_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('reaction_types')
                    ->nullOnDelete();
            }

            $table->index(['post_id', 'reaction_type_id'], 'reactions_post_reaction_type_idx');
            $table->unique(['post_id', 'reaction_type_id', 'user_id'], 'reactions_post_reaction_type_user_unique');
            $table->unique(['post_id', 'reaction_type_id', 'fingerprint'], 'reactions_post_reaction_type_fp_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reactions', function (Blueprint $table) {
            if (Schema::hasColumn('reactions', 'reaction_type_id')) {
                $table->dropConstrainedForeignId('reaction_type_id');
            }
        });

        Schema::dropIfExists('reaction_types');
    }
};
