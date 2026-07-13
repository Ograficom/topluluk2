<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cookie_policies', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->default('Çerez Politikası');
            $table->string('banner_title')->nullable();
            $table->text('banner_message')->nullable();
            $table->longText('content');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });

        DB::table('cookie_policies')->insert([
            'title' => 'Çerez Politikası',
            'banner_title' => 'Çerezleri Kullanıyoruz',
            'banner_message' => 'Deneyiminizi iyileştirmek, güvenliği sağlamak ve istatistik toplamak için çerezleri kullanıyoruz.',
            'content' => <<<HTML
<h2>Çerez Politikası</h2>
<p>Bu sitede oturum açma, güvenlik, tercihler ve istatistik amaçlarıyla çerezler kullanılır. Tarayıcınızın ayarları üzerinden çerez kullanımını yönetebilirsiniz. Çerezleri reddetmeniz bazı özelliklerin çalışmamasına yol açabilir.</p>
<h3>Zorunlu Çerezler</h3>
<p>Oturumunuzu sürdürmek, güvenliği sağlamak ve temel site fonksiyonlarını çalıştırmak için gereklidir.</p>
<h3>İstatistik ve Performans</h3>
<p>Siteyi nasıl kullandığınızı anlamak ve iyileştirmek için anonimleştirilmiş ölçümler toplar.</p>
<h3>Veri Paylaşımı</h3>
<p>Verileriniz üçüncü taraflarla satılmaz; yalnızca hizmet sağlayıcılarımızla sözleşmeye uygun şekilde paylaşılır.</p>
HTML,
            'is_enabled' => true,
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_policies');
    }
};
