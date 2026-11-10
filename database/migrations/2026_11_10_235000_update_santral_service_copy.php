<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = DB::table('santral_page_settings')->where('id', 1)->first();

        if (! $row || ! $row->features) {
            return;
        }

        $features = json_decode($row->features, true);

        if (! is_array($features)) {
            return;
        }

        $serviceCopy = [
            ['icon' => 'channels', 'title' => 'Bulut santral hizmeti', 'description' => 'Bulut santral altyapısını biz sağlıyor, işletmenizin hatlarını, dahili kullanıcılarını ve çağrı yönlendirmelerini sizin adınıza kurup yönetiyoruz.'],
            ['icon' => 'list', 'title' => 'IVR ve profesyonel karşılama', 'description' => 'İşletmenize özel sesli karşılama ve tuşlama menülerini hazırlıyor, aramaları doğru departman ve kişilere yönlendiriyoruz.'],
            ['icon' => 'megaphone', 'title' => '0850 ve şehir numaraları', 'description' => '0850, 0312, 0212, 0216 ve 0232 numara talepleriniz için başvuru, operatör bağlantısı ve santral tanımlamasını sağlıyoruz.'],
            ['icon' => 'message', 'title' => 'WhatsApp Business entegrasyonu', 'description' => 'WhatsApp Business bağlantısını ve müşteri iletişim akışlarını işletmenizin kullandığı süreçlere göre yapılandırıyoruz.'],
            ['icon' => 'security', 'title' => 'SMS ve OTP entegrasyonu', 'description' => 'Doğrulama kodu ve bilgilendirme mesajları için SMS/OTP bağlantılarını kuruyor, test ediyor ve devreye alıyoruz.'],
            ['icon' => 'bolt', 'title' => 'CRM, API ve Webhook bağlantıları', 'description' => 'Santral çağrı olaylarını CRM ve özel yazılımlarınıza bağlıyor; ihtiyaç duyulan API ve Webhook akışlarını yapılandırıyoruz.'],
        ];

        foreach ($features as $index => &$feature) {
            if (isset($serviceCopy[$index])) {
                $feature = array_merge($feature, $serviceCopy[$index]);
            }
        }
        unset($feature);

        DB::table('santral_page_settings')
            ->where('id', 1)
            ->update(['features' => json_encode($features, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
    }

    public function down(): void
    {
        // Content changes are intentionally not reverted to avoid restoring misleading service copy.
    }
};
