<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('description', 255)->nullable();
            $table->string('icon', 120)->nullable();
            $table->string('color', 32)->default('#2563eb');
            $table->unsignedInteger('min_points')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('badges')->insert([
            [
                'name' => 'Mavi Tik',
                'slug' => 'mavi-tik',
                'description' => 'Dogrulanmis seviye rozet.',
                'icon' => 'heroicon-m-check-badge',
                'color' => '#2563eb',
                'min_points' => 1000,
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bronz Uye',
                'slug' => 'bronz-uye',
                'description' => 'Topluluga aktif katilim rozet.',
                'icon' => 'heroicon-m-star',
                'color' => '#b45309',
                'min_points' => 250,
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gumus Uye',
                'slug' => 'gumus-uye',
                'description' => 'Yuksek katilim seviyesi.',
                'icon' => 'heroicon-m-star',
                'color' => '#6b7280',
                'min_points' => 750,
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Altin Uye',
                'slug' => 'altin-uye',
                'description' => 'En ust seviye topluluk rozetlerinden biri.',
                'icon' => 'heroicon-m-trophy',
                'color' => '#d97706',
                'min_points' => 1500,
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
