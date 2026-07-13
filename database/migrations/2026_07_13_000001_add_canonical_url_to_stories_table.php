<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('stories', 'canonical_url')) {
            Schema::table('stories', function (Blueprint $table) {
                $table->string('canonical_url', 512)->nullable()->after('body_rendered');
                $table->index('canonical_url', 'stories_canonical_url_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stories', 'canonical_url')) {
            Schema::table('stories', function (Blueprint $table) {
                $table->dropIndex('stories_canonical_url_index');
                $table->dropColumn('canonical_url');
            });
        }
    }
};
