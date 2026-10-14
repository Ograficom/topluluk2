<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The table may already exist on installations created before this
        // migration was registered. In that case, mark the migration as run
        // instead of attempting CREATE TABLE again.
        if (Schema::hasTable('web_vital_metrics')) {
            return;
        }

        Schema::create('web_vital_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name', 16);
            $table->decimal('metric_value', 14, 4);
            $table->string('rating', 24);
            $table->string('device_type', 16);
            $table->string('page_path', 2048);
            $table->string('route_name', 160)->nullable();
            $table->string('connection_type', 32)->nullable();
            $table->string('navigation_type', 32)->nullable();
            $table->boolean('is_secure')->default(true);
            $table->timestamps();

            $table->index(['metric_name', 'device_type', 'created_at']);
            // Indexing a 2,048-character utf8mb4 path with created_at exceeds
            // MariaDB/MySQL's 3,072-byte composite-index limit. Dashboard
            // queries filter by date before grouping paths, so index that
            // filter directly and keep the full path available for grouping.
            $table->index('created_at');
            $table->index(['rating', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_vital_metrics');
    }
};
