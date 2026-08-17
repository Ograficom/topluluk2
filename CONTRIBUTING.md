# Ografi'ye Katkıda Bulunma

Katkınız için teşekkür ederiz. Ografi özel mülkiyetli bir projedir; deponun herkese açık olması kaynak kodun serbest kullanımına, yeniden dağıtımına veya ticari kullanımına izin vermez. Ayrıntılar için [LICENSE](LICENSE) dosyasını okuyun.

## Başlamadan önce

- Büyük değişikliklerden önce bir özellik isteği veya tartışma açın.
- Hata bildirirken mevcut issue'ları kontrol edin.
- Güvenlik açıklarını herkese açık issue olarak bildirmeyin; [SECURITY.md](SECURITY.md) sürecini kullanın.
- [Davranış Kuralları](CODE_OF_CONDUCT.md) tüm katkılar için geçerlidir.

## Geliştirme ortamı

Gereksinimler:

- PHP 8.3 veya üzeri
- Composer
- Node.js ve npm
- Desteklenen bir MySQL veritabanı

Kurulum:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

Yerel geliştirme:

```bash
php artisan serve
npm run dev
```

## Dal ve commit düzeni

- Çalışmanızı güncel `main` dalından ayrılan kısa ömürlü bir dalda yapın.
- Dal adını değişikliği anlatacak biçimde seçin: `fix/...`, `feature/...` veya `docs/...`.
- Her commit tek bir mantıksal değişiklik içersin.
- Üretilmiş dosyaları, gizli anahtarları, `.env` dosyasını veya kişisel verileri commit etmeyin.
- İlgisiz biçimlendirme ve refactor değişikliklerini aynı PR'a eklemeyin.

## Kod standartları

- Mevcut Laravel, Blade, Tailwind ve JavaScript kalıplarını izleyin.
- PHP biçimlendirmesi için Laravel Pint kullanın.
- Kullanıcı arayüzlerinde açık/koyu tema, mobil görünüm, klavye erişimi ve odak durumlarını kontrol edin.
- Yeni davranışlar için riskle orantılı test ekleyin.
- Veritabanı değişikliklerini geri alınabilir migration olarak hazırlayın.

## Doğrulama

PR açmadan önce ilgili kontrolleri çalıştırın:

```bash
composer test
npm run build
```

PHP biçimlendirmesi değiştiyse:

```bash
vendor/bin/pint --test
```

Tüm testleri çalıştıramadıysanız PR açıklamasında hangi kontrollerin eksik kaldığını ve nedenini belirtin.

## Issue açma

Issue formundaki gerekli alanları doldurun. Hata raporuna şunları ekleyin:

- Beklenen ve gerçekleşen davranış
- Tekrarlanabilir adımlar
- Etkilenen sayfa veya bileşen
- Tarayıcı, cihaz ve ortam bilgisi
- Hassas veri içermeyen log veya ekran görüntüsü

Destek talepleri ile güvenlik raporlarını hata issue'su olarak açmayın.

## Pull request açma

- PR başlığı değişikliği açıkça anlatsın.
- İlgili issue'yu bağlayın.
- Davranış değişikliğini ve teknik yaklaşımı özetleyin.
- Test sonuçlarını yazın.
- Arayüz değişikliklerinde masaüstü, mobil, açık ve koyu tema doğrulamasını belirtin.
- Kırıcı değişiklikleri, migration gereksinimlerini ve dağıtım notlarını açıkça işaretleyin.

Bir katkının gönderilmesi kabul edileceğini garanti etmez. Proje yöneticileri kapsam, kalite, güvenlik ve ürün yönü gerekçeleriyle değişiklik isteyebilir veya katkıyı reddedebilir.

## Katkı hakları

Bir katkı göndererek katkıyı paylaşmaya yetkili olduğunuzu beyan eder ve Ografi'ye katkıyı proje kapsamında kullanma, çoğaltma, değiştirme, birleştirme, yayımlama ve dağıtma konusunda süresiz, dünya çapında ve bedelsiz bir lisans verirsiniz. Bu hüküm ana projenin özel mülkiyet statüsünü değiştirmez.
