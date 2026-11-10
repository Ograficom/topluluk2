<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santral_page_settings', function (Blueprint $table): void {
            $table->string('packages_title')->default('İşletmeniz için uygun fiyatlı hazır santral paketleri');
            $table->text('packages_description')->nullable();
            $table->json('packages')->nullable();
        });

        DB::table('santral_page_settings')
            ->where('steps_title', 'Kurulumla uğraşmayın, biz yönetelim')
            ->update(['steps_title' => 'Uygun fiyatlı, hazır bulut santral']);

        DB::table('santral_page_settings')
            ->where('steps_title', 'Nasıl çalışır?')
            ->update(['steps_title' => 'Hazır santral hizmetiniz nasıl başlar?']);
    }

    public function down(): void
    {
        Schema::table('santral_page_settings', function (Blueprint $table): void {
            $table->dropColumn(['packages_title', 'packages_description', 'packages']);
        });
    }
};
