<?php

use App\Services\LiveChannelPlaylistService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('live_channels')) {
            Schema::create('live_channels', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('category', 80)->default('Genel')->index();
                $table->text('stream_url');
                $table->string('featured_image')->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        app(LiveChannelPlaylistService::class)
            ->syncFromFile(public_path('streams/turkiye.m3u'));
    }

    public function down(): void
    {
        // Bu migration veri onarımı içindir. Kullanıcının Filament'te yaptığı
        // kanal ve görsel düzenlemelerini rollback sırasında silmeyiz.
    }
};
