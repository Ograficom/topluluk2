# OgraFi Google Drive kurulumu

- Canlı MariaDB sunucuda kalır; Google Drive veritabanı motoru değildir.
- local ve public diskleri korunur. Varsayılan disk Google Drive yapılmaz.
- İlk bağlantı Drive içinde OGRAFI_STORAGE ana klasörünü oluşturur.
- Bu aşama mevcut medyayı taşımaz ve silmez.

## OAuth ayarları

Authorized JavaScript origin: https://ografi.com

Authorized redirect URI: https://ografi.com/auth/google-drive/callback

Scope: https://www.googleapis.com/auth/drive.file

OAuth Client type: Web application. Test sırasında Gmail hesabını OAuth Audience > Test users listesine ekleyin. Üretim öncesinde Google OAuth yayınlama/doğrulama gerekliliklerini tamamlayın.

## Kurulum

```bash
composer require google/apiclient:^2.18
```

Sunucu .env dosyasına ekleyin (değerleri Git'e göndermeyin):

```dotenv
GOOGLE_DRIVE_ENABLED=true
GOOGLE_DRIVE_CLIENT_ID=
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REDIRECT_URI=https://ografi.com/auth/google-drive/callback
GOOGLE_DRIVE_ROOT_FOLDER=OGRAFI_STORAGE
GOOGLE_DRIVE_BACKUP_ENABLED=true
GOOGLE_DRIVE_BACKUP_RETENTION=30
```

Ardından:

```bash
php artisan migrate --force
php artisan optimize:clear
```

Yönetim panelinde /admin/google-drive sayfasını açıp Google hesabını bağlayın.

## Önemli

Bu ilk aşama bağlantı temelidir. Mevcut uygulamada bazı dosyalar public URL, local path ve doğrudan Storage::path() ile kullanılıyor. Bu yüzden tüm medyayı taşımadan önce her özellik için Drive file ID tabanlı erişim, yetkilendirme, CDN/cache ve resumable upload katmanı eklenmelidir. Büyük dosyalar PHP belleğine bütünüyle alınmamalıdır. Yedekler Drive'a yüklenmeden önce şifrelenmeli ve geri yükleme testi yapılmalıdır.

Access/refresh tokenları Eloquent encrypted casts ile şifrelenir. APP_KEY kaybedilirse tokenlar çözülemez.
