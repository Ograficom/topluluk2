# Repost

Repost, sağlanan HTML tasarımını Laravel 12 ve Filament yönetim paneliyle birleştiren bir içerik topluluğu uygulamasıdır.

## Gereksinimler

- PHP 8.3 veya üzeri
- Composer
- SQLite (PHP `pdo_sqlite` eklentisi)

## Kurulum

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
php artisan key:generate
php artisan storage:link
```

`.env` dosyasındaki `FILAMENT_ADMIN_EMAIL` ve `FILAMENT_ADMIN_PASSWORD` alanlarına bir yönetici e-posta adresi ve güçlü bir parola girin. Ardından tabloları ve örnek içerikleri oluşturun:

```powershell
php artisan migrate --seed
php artisan serve
```

Uygulama `http://127.0.0.1:8000` adresinde, yönetim paneli ise `/admin` adresinde açılır. Yönetici kullanıcı yalnızca bu kimlik bilgileri ayarlanıp veritabanı tohumlandığında oluşturulur. Sonradan yönetici bilgisi eklemek için `.env` değerlerini doldurup `php artisan db:seed` komutunu çalıştırın.

## İçerik ve yönetim

- Ana akışta yayınlanmış yazılar listelenir; arama, sayfalama ve yazı ayrıntı sayfası bulunur.
- Kanallar listelenebilir, her kanalın yayınlanmış yazıları ayrı sayfada görüntülenebilir.
- Filament panelinde yazı ve kanal oluşturma, düzenleme, silme ve yayın durumunu yönetme ekranları bulunur.
- Panel erişimi `users.is_admin` alanı ile sınırlandırılmıştır.
- Örnek kanallar ve yazılar `php artisan db:seed` ile yüklenir.

## Üyelik ve etkileşimler

- `/signup` üzerinden hesap oluşturulabilir; kullanıcı adı veya e-posta ile `/signin` üzerinden giriş yapılabilir.
- Giriş yapan üyeler üst menüdeki **Yaz** bağlantısıyla yazı oluşturabilir; kategori/kanal oluşturma formu yalnızca yöneticilere açıktır ve Kanallar sayfasında görünür.
- Üyeler yazılara yorum bırakabilir, beğeni/beğenmeme tepkisi verebilir ve profil bilgileriyle hesap tercihlerini `/setting.html` sayfasından yönetebilir.
- Görüntülenme sayısı yazı ayrıntı sayfasında artırılır.
- Doğrulama rozeti için ayarlardaki Verification bölümünden başvuru gönderilir; yönetici başvuruyu Filament kullanıcı ekranından onaylayabilir veya reddedebilir.
- Ayarlar ayrıca profil görseli, sosyal bağlantılar, saat dilimi, bildirim tercihleri, 18+ içerik tercihi, parola/e-posta, kara liste ve hesap silme kontrollerini içerir.

## Şablon

Repost HTML şablonunun CSS, JavaScript, görsel ve font dosyaları `public/assets` altına kopyalanmıştır. Uygulama ana akış, yazı ve kanal sayfalarında bu tasarımın stillerini kullanır. Şablonun orijinal dosyaları `Repost/dist` altında korunur.

## Testler

```powershell
php artisan test
```
