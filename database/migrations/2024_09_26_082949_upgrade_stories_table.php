<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $supportsFullText = Schema::getConnection()->getDriverName() !== 'sqlite';

        Schema::table('stories', function (Blueprint $table) use ($supportsFullText) {
            if (Schema::hasColumn('stories', 'comment_visibility')) {
                $table->dropIndex(['comment_visibility']);
                $table->dropColumn('comment_visibility');
            }
            $table->unsignedBigInteger('views_count')->default(0)->index()->after('meta');
            $table->boolean('is_comments_disabled')->default(false)->after('featured');
            $table->boolean('is_nsfw')->default(false)->after('is_comments_disabled');
            if ($supportsFullText) {
                $table->fullText('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $supportsFullText = Schema::getConnection()->getDriverName() !== 'sqlite';

        Schema::table('stories', function (Blueprint $table) use ($supportsFullText) {
            $table->dropColumn('views_count');
            $table->dropColumn('is_comments_disabled');
            $table->dropColumn('is_nsfw');
            if ($supportsFullText) {
                $table->dropFullText('title');
            }
        });
    }
};
