# Ografi

Ografi; haber, makale, topluluk gönderileri ve sosyal etkileşim özelliklerini tek bir platformda birleştiren Laravel tabanlı bir içerik ve sosyal paylaşım projesidir.

Kullanıcılar içerik yayımlayabilir, diğer kullanıcıları takip edebilir, gönderilere tepki verebilir, yorum yapabilir, içerikleri kaydedebilir ve farklı kategoriler altında güncel paylaşımları keşfedebilir.

> Proje sitesi: [ografi.com](https://ografi.com)

---

## İçindekiler

- [Proje Hakkında](#proje-hakkında)
- [Özellikler](#özellikler)
- [Kullanılan Teknolojiler](#kullanılan-teknolojiler)
- [Sistem Gereksinimleri](#sistem-gereksinimleri)
- [Yerel Kurulum](#yerel-kurulum)
- [Ortam Değişkenleri](#ortam-değişkenleri)
- [Veritabanı Kurulumu](#veritabanı-kurulumu)
- [Depolama Bağlantıları](#depolama-bağlantıları)
- [Frontend Kurulumu](#frontend-kurulumu)
- [Kuyruk Sistemi](#kuyruk-sistemi)
- [Zamanlanmış Görevler](#zamanlanmış-görevler)
- [E-posta Ayarları](#e-posta-ayarları)
- [Yönetim Paneli](#yönetim-paneli)
- [Sunucuya Kurulum](#sunucuya-kurulum)
- [Nginx Yapılandırması](#nginx-yapılandırması)
- [Dosya İzinleri](#dosya-izinleri)
- [Güncelleme ve Bakım](#güncelleme-ve-bakım)
- [Sorun Giderme](#sorun-giderme)
- [Güvenlik](#güvenlik)
- [Lisans](#lisans)

---

## Proje Hakkında

Ografi, klasik haber sitelerinin içerik yapısını sosyal medya etkileşimleriyle birleştirmek amacıyla geliştirilmiştir.

Platform aşağıdaki temel içerik türlerini destekleyecek şekilde tasarlanmıştır:

- Haber ve makale içerikleri
- Kullanıcı gönderileri
- Görsel ve bağlantı paylaşımları
- Kategori ve etiket sayfaları
- Kullanıcı profilleri
- Yorumlar ve yanıtlar
- Beğeni ve tepki sistemi
- Takipçi ve takip sistemi
- Bildirimler
- Kaydedilen içerikler
- Reklam alanları
- Yönetim paneli

---

## Özellikler

### İçerik yönetimi

- Gönderi, haber ve makale oluşturma
- Editor.js tabanlı gelişmiş içerik editörü
- Kapak görseli ve medya desteği
- Kategori ve etiket sistemi
- Taslak, yayımlanmış ve planlanmış içerik durumları
- İçerik düzenleme geçmişi
- Harici kaynak bağlantıları
- Okunma ve görüntülenme istatistikleri
- SEO uyumlu bağlantılar
- Yapılandırılmış veri desteği

### Sosyal özellikler

- Kullanıcı profilleri
- Kullanıcı takip sistemi
- Gönderi beğenme ve tepki verme
- Yorum ve yanıt sistemi
- Gönderi kaydetme
- Gönderi paylaşma
- Kullanıcı ve kategori hover kartları
- Bildirim sistemi
- Sosyal medya bağlantıları
- Kullanıcı rozetleri

### Arayüz

- Mobil uyumlu tasarım
- Üç sütunlu masaüstü yerleşimi
- Açık ve koyu tema desteği
- Tailwind CSS tabanlı bileşenler
- Yükleme iskeletleri
- Sabit yan menüler
- Mobil alt navigasyon
- Erişilebilirlik araçları
- Responsive görsel ve video alanları

### Yönetim paneli

- Kullanıcı yönetimi
- İçerik yönetimi
- Kategori ve etiket yönetimi
- Yorum yönetimi
- Reklam yönetimi
- Bildirim yönetimi
- Sistem ayarları
- Aktivite kayıtları
- Rol ve yetki yönetimi

### İsteğe bağlı özellikler

- RSS kaynaklarından içerik alma
- Yapay zekâ destekli içerik yeniden yazımı
- Otomatik özet ve başlık üretimi
- Zamanlanmış içerik yayımlama
- Harici API entegrasyonları

---

## Kullanılan Teknolojiler

### Backend

- PHP
- Laravel
- FilamentPHP
- MySQL veya MariaDB
- Laravel Queue
- Laravel Scheduler
- Laravel Notifications

### Frontend

- Blade
- Tailwind CSS
- JavaScript
- Vite
- Editor.js
- Lucide veya Heroicons ikonları

### Sunucu

- Ubuntu
- Nginx
- PHP-FPM
- MySQL veya MariaDB
- Redis isteğe bağlı
- Supervisor
- Cron
- aaPanel ile uyumlu kurulum

---

## Sistem Gereksinimleri

Projeyi kurmadan önce sunucunuzda aşağıdaki bileşenlerin bulunduğundan emin olun:

- PHP 8.3 veya üzeri
- Composer 2
- Node.js 20 veya üzeri
- npm
- MySQL 8 veya MariaDB 10.6 ve üzeri
- Nginx veya Apache
- Git
- PHP-FPM

Gerekli PHP eklentileri:

```text
bcmath
ctype
curl
dom
fileinfo
filter
gd
intl
mbstring
openssl
pdo
pdo_mysql
session
tokenizer
xml
zip
```

PHP sürümünü kontrol etmek için:

```bash
php -v
```

Composer sürümünü kontrol etmek için:

```bash
composer --version
```

Node.js ve npm sürümlerini kontrol etmek için:

```bash
node -v
npm -v
```

---

## Yerel Kurulum

Projeyi bilgisayarınıza indirin:

```bash
git clone https://github.com/kullanici-adi/ografi.git
cd ografi
```

PHP bağımlılıklarını yükleyin:

```bash
composer install
```

Ortam dosyasını oluşturun:

### Linux veya macOS

```bash
cp .env.example .env
```

### Windows PowerShell

```powershell
Copy-Item .env.example .env
```

Uygulama anahtarını oluşturun:

```bash
php artisan key:generate
```

Önbellekleri temizleyin:

```bash
php artisan optimize:clear
```

---

## Ortam Değişkenleri

`.env` dosyasındaki temel ayarları düzenleyin:

```env
APP_NAME="Ografi"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

APP_LOCALE=tr
APP_FALLBACK_LOCALE=tr
APP_FAKER_LOCALE=tr_TR

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ografi
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=public
```

Canlı sunucuda aşağıdaki değerleri kullanın:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ografi.com
LOG_LEVEL=error
```

> `.env` dosyasını hiçbir zaman Git deposuna göndermeyin.

---

## Veritabanı Kurulumu

Öncelikle boş bir veritabanı oluşturun:

```sql
CREATE DATABASE ografi
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Migration dosyalarını çalıştırın:

```bash
php artisan migrate
```

Başlangıç verileri bulunuyorsa seeder çalıştırın:

```bash
php artisan db:seed
```

Migration ve seeder işlemlerini birlikte çalıştırmak için:

```bash
php artisan migrate --seed
```

Canlı sunucuda migration çalıştırırken:

```bash
php artisan migrate --force
```

Veritabanını tamamen sıfırlamak için:

```bash
php artisan migrate:fresh --seed
```

> `migrate:fresh` bütün tabloları siler. Canlı sunucuda kullanmayın.

---

## Depolama Bağlantıları

Laravel public storage bağlantısını oluşturun:

```bash
php artisan storage:link
```

Projede özel yükleme dizinleri kullanılıyorsa aşağıdaki klasörlerin mevcut olduğundan emin olun:

```bash
mkdir -p storage/app/public
mkdir -p storage/app/uploads
mkdir -p storage/app/temp
mkdir -p storage/logs
mkdir -p bootstrap/cache
```

Bağlantı zaten varsa kontrol edin:

```bash
ls -la public/storage
```

Bozuk bağlantıyı yenilemek için:

```bash
rm -f public/storage
php artisan storage:link
```

Özel `uploads` ve `temp` bağlantıları kullanılıyorsa:

```bash
ln -sfn "$(pwd)/storage/app/uploads" public/uploads
ln -sfn "$(pwd)/storage/app/temp" public/temp
```

---

## Frontend Kurulumu

Node.js bağımlılıklarını yükleyin:

```bash
npm install
```

Geliştirme sunucusunu başlatın:

```bash
npm run dev
```

Canlı sunucu dosyalarını oluşturun:

```bash
npm run build
```

Vite manifest dosyasının oluştuğunu kontrol edin:

```bash
ls -la public/build/manifest.json
```

Yerel geliştirme sırasında Laravel sunucusunu başlatın:

```bash
php artisan serve
```

Uygulama varsayılan olarak şu adreste açılır:

```text
http://127.0.0.1:8000
```

---

## Kuyruk Sistemi

Kuyruk tabloları henüz oluşturulmadıysa:

```bash
php artisan queue:table
php artisan failed:table
php artisan migrate
```

Kuyruk çalışanını başlatın:

```bash
php artisan queue:work
```

Canlı sunucuda:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=120
```

Başarısız işleri görüntülemek için:

```bash
php artisan queue:failed
```

Başarısız işi yeniden çalıştırmak için:

```bash
php artisan queue:retry all
```

Kuyruk çalışanlarını yeniden başlatmak için:

```bash
php artisan queue:restart
```

### Supervisor örneği

```ini
[program:ografi-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /www/wwwroot/ografi.com/public/artisan queue:work --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www
numprocs=1
redirect_stderr=true
stdout_logfile=/www/wwwroot/ografi.com/public/storage/logs/worker.log
stopwaitsecs=3600
```

Supervisor yapılandırmasını yenileyin:

```bash
supervisorctl reread
supervisorctl update
supervisorctl restart ografi-worker:*
```

---

## Zamanlanmış Görevler

Laravel zamanlayıcısının çalışması için sunucuya cron görevi ekleyin:

```bash
* * * * * cd /www/wwwroot/ografi.com/public && /www/server/php/83/bin/php artisan schedule:run >> /dev/null 2>&1
```

Cron görevlerini görüntülemek için:

```bash
php artisan schedule:list
```

Zamanlayıcıyı elle çalıştırmak için:

```bash
php artisan schedule:run
```

---

## E-posta Ayarları

SMTP ayarlarını `.env` dosyasına ekleyin:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.ografi.com
MAIL_PORT=465
MAIL_USERNAME=smtp@ografi.com
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=smtp@ografi.com
MAIL_FROM_NAME="${APP_NAME}"
```

587 portu ve STARTTLS kullanılıyorsa:

```env
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

Ayarları değiştirdikten sonra:

```bash
php artisan optimize:clear
```

E-posta gönderimini Tinker ile test edin:

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Ografi SMTP testi başarılı.', function ($message) {
    $message
        ->to('test@example.com')
        ->subject('Ografi SMTP Test');
});
```

---

## Yönetim Paneli

Yönetim paneli varsayılan olarak aşağıdaki adreste bulunabilir:

```text
https://ografi.com/admin
```

Yeni bir kullanıcı oluşturmak için uygulamanın kendi kayıt sistemi veya yönetim komutu kullanılmalıdır.

Filament kullanıcısı oluşturmak için:

```bash
php artisan make:filament-user
```

Rol ve izin sistemi kullanılıyorsa kullanıcıya gerekli yönetici rolünü atayın.

Örnek:

```bash
php artisan tinker
```

```php
$user = App\Models\User::where('email', 'admin@example.com')->firstOrFail();
$user->assignRole('super-admin');
```

> Yönetici parolalarını README dosyasına veya Git deposuna eklemeyin.

---

## Sunucuya Kurulum

Aşağıdaki örnek, aaPanel üzerinde bulunan standart Ografi kurulum dizinine göre hazırlanmıştır:

```text
/www/wwwroot/ografi.com/public
```

Proje dizinine geçin:

```bash
cd /www/wwwroot/ografi.com/public
```

Bakım modunu açın:

```bash
php artisan down
```

Güncel kodları alın:

```bash
git pull origin main
```

PHP bağımlılıklarını yükleyin:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```

Frontend dosyalarını oluşturun:

```bash
npm ci
npm run build
```

Migration işlemlerini çalıştırın:

```bash
php artisan migrate --force
```

Depolama bağlantısını kontrol edin:

```bash
php artisan storage:link
```

Önbellekleri temizleyip yeniden oluşturun:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Kuyruk çalışanlarını yeniden başlatın:

```bash
php artisan queue:restart
```

Bakım modunu kapatın:

```bash
php artisan up
```

---

## Nginx Yapılandırması

Örnek Nginx yapılandırması:

```nginx
server {
    listen 80;
    listen [::]:80;

    server_name ografi.com www.ografi.com;

    root /www/wwwroot/ografi.com/public/public;
    index index.php index.html;

    charset utf-8;

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/tmp/php-cgi-83.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(jpg|jpeg|png|gif|webp|svg|ico|css|js|woff|woff2|ttf|eot)$ {
        expires 30d;
        access_log off;
        add_header Cache-Control "public, no-transform";
    }
}
```

> Proje dizini doğrudan `/www/wwwroot/ografi.com/public` ise Nginx `root` değeri Laravel’in gerçek `public` klasörünü göstermelidir.

Yapılandırmayı test edin:

```bash
nginx -t
```

Nginx servisini yeniden yükleyin:

```bash
systemctl reload nginx
```

---

## Dosya İzinleri

Laravel’in yazabilmesi gereken dizinler:

```text
storage
bootstrap/cache
```

aaPanel üzerinde örnek izinler:

```bash
cd /www/wwwroot/ografi.com/public

chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

Gerekli klasörleri yeniden oluşturmak için:

```bash
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
```

Ardından:

```bash
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

Laravel log dosyası yazılamıyorsa:

```bash
touch storage/logs/laravel.log
chown www:www storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
```

---

## Güncelleme ve Bakım

### Genel bakım komutları

```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Üretim önbellekleri

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Composer paketlerini kontrol etme

```bash
composer outdated
```

### npm paketlerini kontrol etme

```bash
npm outdated
```

### Laravel sürümünü öğrenme

```bash
php artisan --version
```

### Filament sürümünü öğrenme

```bash
composer show filament/filament
```

### Uygulama hakkında bilgi alma

```bash
php artisan about
```

---

## Sorun Giderme

### Uygulama anahtarı bulunamadı

Hata:

```text
No application encryption key has been specified.
```

Çözüm:

```bash
php artisan key:generate
php artisan optimize:clear
```

### Site beyaz ekran gösteriyor

Geçici olarak `.env` dosyasında:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Ardından:

```bash
php artisan optimize:clear
tail -f storage/logs/laravel.log
```

Sorun çözüldükten sonra:

```env
APP_DEBUG=false
```

### Laravel log dosyasına yazılamıyor

Hata:

```text
Permission denied
```

Çözüm:

```bash
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Storage bağlantısı zaten mevcut

Hata:

```text
The [public/storage] link already exists.
```

Bağlantıyı kontrol edin:

```bash
readlink -f public/storage
```

Bağlantı bozuksa:

```bash
rm -f public/storage
php artisan storage:link
```

### Vite manifest bulunamadı

Hata:

```text
Vite manifest not found
```

Çözüm:

```bash
npm install
npm run build
```

Ardından:

```bash
php artisan optimize:clear
```

### PHP sürümü yetersiz

Hata:

```text
Your Composer dependencies require a PHP version
```

aaPanel PHP 8.3 ile komut çalıştırın:

```bash
/www/server/php/83/bin/php artisan about
```

Composer’ı aynı PHP sürümüyle çalıştırmak için:

```bash
/www/server/php/83/bin/php /usr/bin/composer install
```

Composer yolu farklıysa:

```bash
which composer
```

### Class finfo not found

PHP `fileinfo` eklentisini etkinleştirin ve PHP-FPM servisini yeniden başlatın.

### 500 sunucu hatası

Aşağıdaki komutları çalıştırın:

```bash
php artisan optimize:clear
composer dump-autoload
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
tail -n 100 storage/logs/laravel.log
```

---

## Güvenlik

- `.env` dosyasını Git deposuna eklemeyin.
- API anahtarlarını kaynak kod içine yazmayın.
- Canlı sunucuda `APP_DEBUG=false` kullanın.
- Yönetici hesaplarında güçlü parola kullanın.
- Veritabanını düzenli olarak yedekleyin.
- Yüklenen dosyaların MIME türlerini doğrulayın.
- Kullanıcı girdilerini doğrulayın.
- Yetkilendirme işlemlerinde policy ve gate kullanın.
- Yönetim paneline rol tabanlı erişim uygulayın.
- Laravel ve bağımlılıklarını güncel tutun.
- SMTP, veritabanı ve üçüncü taraf servis parolalarını düzenli olarak yenileyin.
- Sunucuda yalnızca gerekli portları açık bırakın.

Bir güvenlik açığı tespit edilirse herkese açık issue oluşturmadan proje yöneticisiyle doğrudan iletişime geçilmelidir.

---

## Yedekleme

Veritabanı yedeği almak için:

```bash
mysqldump -u veritabani_kullanicisi -p ografi > ografi-backup.sql
```

Dosya yedeği için:

```bash
tar -czf ografi-files-backup.tar.gz storage/app/public storage/app/uploads
```

Yedeklere `.env` dosyasını eklerken dosyanın güvenli biçimde saklandığından emin olun.

---

## Kod Standartları

Kod biçimlendirme için Laravel Pint kullanılabilir:

```bash
./vendor/bin/pint
```

Testleri çalıştırmak için:

```bash
php artisan test
```

Belirli bir testi çalıştırmak için:

```bash
php artisan test --filter=TestAdi
```

Yeni özellik geliştirirken:

- Laravel servis yapısını kullanın.
- Form doğrulamalarını Form Request sınıflarında tutun.
- Yetkilendirme için Policy kullanın.
- Uzun süren işlemleri kuyruğa gönderin.
- N+1 sorgularını önlemek için eager loading kullanın.
- Arayüz değişikliklerinde mobil görünümü kontrol edin.
- Mevcut açık ve koyu tema uyumluluğunu koruyun.

---

## Katkıda Bulunma

1. Depoyu fork edin.
2. Yeni bir özellik dalı oluşturun.

```bash
git checkout -b feature/yeni-ozellik
```

3. Değişikliklerinizi kaydedin.

```bash
git add .
git commit -m "Yeni özellik eklendi"
```

4. Dalı uzak depoya gönderin.

```bash
git push origin feature/yeni-ozellik
```

5. Pull request oluşturun.

---

## Proje Sahibi

Ografi, Enes Bodur tarafından geliştirilen bir içerik ve sosyal topluluk projesidir.

- Website: [ografi.com](https://ografi.com)
- E-posta: [smtp@ografi.com](mailto:smtp@ografi.com)

---

## Lisans

Bu proje özel mülkiyetli bir yazılımdır.

Kaynak kodun izinsiz şekilde kopyalanması, dağıtılması, satılması, yeniden yayımlanması veya başka bir projede kullanılması yasaktır.

```text
Copyright © 2026 Ografi. Tüm hakları saklıdır.
```
