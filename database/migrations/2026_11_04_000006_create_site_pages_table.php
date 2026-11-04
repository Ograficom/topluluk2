<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        $now = now();

        DB::table('site_pages')->insert([
            [
                'title' => 'Hakkımızda',
                'slug' => 'about',
                'content' => '<p>OgraFi; insanların ilgi alanlarını, deneyimlerini ve fikirlerini paylaşabildiği bir topluluk platformudur.</p><p>Kanalları keşfedebilir, yazılar yayımlayabilir, yorum yapabilir ve diğer üyelerle iletişim kurabilirsiniz.</p>',
                'meta_title' => 'Hakkımızda',
                'meta_description' => 'OgraFi topluluk platformunu tanıyın.',
                'is_published' => true,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Topluluk kuralları',
                'slug' => 'rules',
                'content' => '<p>Saygılı olun. Hakaret, tehdit, hedef gösterme ve taciz kabul edilmez.</p><p>Spam, yanıltıcı bağlantılar, izinsiz reklam ve başka kişilerin içeriklerini kaynak göstermeden kopyalamak yasaktır.</p><p>Paylaşımlarınızın hukuka uygun olduğundan ve gerekli haklara sahip olduğunuzdan emin olun.</p><p>Yöneticiler, kuralları ihlal eden içerikleri kaldırabilir ve hesaplara kısıtlama uygulayabilir.</p>',
                'meta_title' => 'Topluluk kuralları',
                'meta_description' => 'OgraFi topluluğunda uyulması gereken kurallar.',
                'is_published' => true,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Gizlilik',
                'slug' => 'privacy',
                'content' => '<p>Hesap ve profil bilgileriniz, platformun temel topluluk özelliklerini sunmak için kullanılır.</p><p>Şifrenizi kimseyle paylaşmayın. Hesabınızın güvenliğinden siz sorumlusunuz.</p><p>Gizlilik ve veri talepleri için iletişim sayfasından site yönetimine ulaşabilirsiniz.</p>',
                'meta_title' => 'Gizlilik politikası',
                'meta_description' => 'OgraFi gizlilik bilgileri.',
                'is_published' => true,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_pages');
    }
};
