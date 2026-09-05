<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'votes_enabled')) {
            return;
        }

        // Oylama artik opt-in. Daha once varsayilan acik geldigi icin mevcut
        // normal postlari otomatik olarak oylama postu saymayalim.
        DB::table('posts')->update(['votes_enabled' => false]);

        Schema::table('posts', function (Blueprint $table): void {
            $table->boolean('votes_enabled')->default(false)->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('posts', 'votes_enabled')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->boolean('votes_enabled')->default(true)->change();
        });
    }
};
