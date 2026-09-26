<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->index(['page_path', 'created_at']);
            $table->index(['rating', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_vital_metrics');
    }
};
