<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique();
            $table->string('label');
            $table->string('value')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $platforms = [
            ['platform' => 'facebook', 'label' => 'Facebook', 'sort_order' => 1],
            ['platform' => 'instagram', 'label' => 'Instagram', 'sort_order' => 2],
            ['platform' => 'x', 'label' => 'X (Twitter)', 'sort_order' => 3],
            ['platform' => 'youtube', 'label' => 'YouTube', 'sort_order' => 4],
            ['platform' => 'whatsapp', 'label' => 'WhatsApp', 'sort_order' => 5],
            ['platform' => 'tiktok', 'label' => 'TikTok', 'sort_order' => 6],
            ['platform' => 'telegram', 'label' => 'Telegram', 'sort_order' => 7],
            ['platform' => 'discord', 'label' => 'Discord', 'sort_order' => 8],
            ['platform' => 'github', 'label' => 'GitHub', 'sort_order' => 9],
        ];

        foreach ($platforms as $platform) {
            DB::table('social_links')->updateOrInsert(
                ['platform' => $platform['platform']],
                [
                    'label' => $platform['label'],
                    'value' => null,
                    'is_active' => false,
                    'sort_order' => $platform['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
