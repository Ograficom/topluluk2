<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santral_page_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('eyebrow')->default('OGRAFI SANTRAL');
            $table->string('hero_title')->default('İşletmenizin iletişimi, tek merkezden.');
            $table->text('hero_description')->nullable();
            $table->string('primary_button_text')->default('Santral hizmeti için iletişime geç');
            $table->string('activation_button_text')->default('Entegrasyona başla');
            $table->text('hero_note')->nullable();
            $table->string('features_title')->default('İşletmenize uygun santral çözümleri');
            $table->json('features')->nullable();
            $table->string('steps_title')->default('Kurulumla uğraşmayın, biz yönetelim');
            $table->json('steps')->nullable();
            $table->string('bottom_title')->default('İşletmeniz için doğru santral çözümünü birlikte kuralım.');
            $table->text('bottom_description')->nullable();
            $table->json('slides')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santral_page_settings');
    }
};
