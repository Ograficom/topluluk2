<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pages')) {
            return;
        }

        $content = <<<'HTML'
<p>Ografi, kullanıcıların haber, makale ve topluluk içerikleri yayımlamasına, keşfetmesine ve bu içeriklerle etkileşim kurmasına olanak tanıyan Türkiye merkezli dijital içerik ve topluluk platformudur. Enes Bodur tarafından kurulan platform, Eabodur Medya Prodüksiyon Limited Şirketi tarafından işletilmektedir. Ografi, 25 Aralık 2025 tarihinde Ankara’da kurulmuştur.</p>
<h2>Ografi nedir?</h2>
<p>Ografi; haberlerin, makalelerin, görüşlerin ve ilgi alanlarına yönelik topluluk paylaşımlarının yayımlanabildiği, keşfedilebildiği ve tartışılabildiği bir dijital platformdur.</p>
<h2>Kuruluş ve sahiplik</h2>
<p>Ografi, Enes Bodur tarafından 25 Aralık 2025 tarihinde Ankara’da kurulmuştur. Platformun işletmecisi Eabodur Medya Prodüksiyon Limited Şirketi’dir.</p>
<h2>İçerik modeli</h2>
<p>Platformda Ografi tarafından hazırlanan içeriklerin yanı sıra kullanıcıların, içerik üreticilerinin ve açıkça belirtilen üçüncü taraf kaynakların içerikleri yer alabilir. İçeriğin kaynağı ve yazarı mümkün olan her durumda görünür biçimde belirtilir.</p>
<h2>Editoryal ilkeler</h2>
<p>Ografi; doğruluk, kaynak şeffaflığı, tarafsızlık, kişilik haklarına saygı ve kamu yararını gözetir. Editoryal içerik ile reklam veya sponsorlu içerik birbirinden açıkça ayrılır.</p>
<h2>Kullanıcı içerikleri ve RSS kaynakları</h2>
<p>Kullanıcı içerikleri, aksi belirtilmedikçe Ografi’nin kurumsal görüşünü temsil etmez. RSS veya üçüncü taraf kaynaklardan alınan içeriklerde kaynak bağlantısı gösterilir; hak ihlali, yanlış bilgi veya güncellik sorunu bildirilen içerikler incelenir.</p>
<h2>Reklam ve finansman modeli</h2>
<p>Ografi, faaliyetlerini reklam, sponsorlu içerik ve dijital hizmet gelirleriyle finanse edebilir. Ticari iş birlikleri editoryal değerlendirmelerden bağımsız tutulur ve reklam niteliğindeki içerikler açıkça işaretlenir.</p>
<h2>Düzeltme ve şikâyet politikası</h2>
<p>Yanlış, eksik veya hak ihlali oluşturduğu düşünülen içerikler iletişim kanalı üzerinden bildirilebilir. Başvurular incelenir; gerekli görülürse içerik düzeltilir, güncellenir, erişimi sınırlandırılır veya kaldırılır.</p>
<h2>İletişim ve şirket künyesi</h2>
<p><strong>İşletmeci:</strong> Eabodur Medya Prodüksiyon Limited Şirketi<br><strong>Kurucu:</strong> Enes Bodur<br><strong>Merkez:</strong> Ankara, Türkiye</p>
<p>Genel iletişim, içerik düzeltme, şikâyet, telif ve kişisel veri başvuruları için <a href="/contact">İletişim sayfasını</a> kullanabilirsiniz.</p>
HTML;

        DB::table('pages')
            ->where('slug', 'hakkimizda')
            ->update([
                'title' => 'Hakkımızda',
                'content' => $content,
                'meta_title' => 'Hakkımızda | Ografi',
                'meta_description' => 'Ografi’nin kuruluşu, sahipliği, içerik modeli, editoryal ilkeleri, finansmanı ve iletişim bilgileri hakkında bilgi edinin.',
                'meta_keywords' => null,
                'noindex' => false,
                'is_published' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Editorial content changes are intentionally not rolled back.
    }
};
