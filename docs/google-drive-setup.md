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
# Optional: absolute paths if mysql client binaries are not in PATH
# MYSQLDUMP_PATH=/usr/bin/mysqldump
# MYSQL_PATH=/usr/bin/mysql
```

Yedekleme için sunucuda PHP sodium eklentisi, Symfony Process'in kullanabildiği process başlatma desteği ve MariaDB/MySQL istemci araçları (mysqldump, mysql) bulunmalıdır. Komutlar yalnızca MySQL/MariaDB bağlantısını destekler.

Ardından:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan schedule:list
```

Yönetim panelinde /admin/google-drive-settings sayfasını açıp Google hesabını bağlayın.

## Şifreli veritabanı yedekleme

Google Drive bağlantısı kurulduktan sonra elle yedek almak için:

```bash
php artisan google-drive:backup
```

Komut MariaDB veritabanını mysqldump ile alır, dosyayı XChaCha20-Poly1305 secretstream ile akış halinde şifreler ve Drive kök klasörüne ofargi-db-*.sql.enc adıyla yükler. Geçici SQL ve şifreli dosyalar işlem sonunda sunucudan silinir. Geçici dosyalar yalnızca sunucu hesabına açık izinlerle tutulur.

Şifreleme anahtarı mevcut Laravel APP_KEY değerinden türetilir. **APP_KEY değişirse veya kaybolursa eski yedekler çözülemez.** APP_KEY'i güvenli ve Drive hesabından bağımsız bir yerde saklayın.

Günlük yedekleme 02:00'de, saklama süresi dolan yedeklerin temizliği 02:30'da planlanır. Sunucunun cron tablosunda Laravel scheduler dakikada bir çalışmalıdır:

```cron
* * * * * cd /www/wwwroot/ografi.com && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

PHP ve proje yollarını sunucudaki gerçek konumlara göre düzenleyin. Elle eski yedek temizliği:

```bash
php artisan google-drive:prune
```

## Geri yükleme testi

Önce MariaDB'de boş bir test veritabanı oluşturun. Canlı veritabanı adını hedef olarak kullanmayın. Ardından Google Drive dosya kimliği ile:

```bash
php artisan google-drive:restore DRIVE_FILE_ID ografi_restore_test --confirm-target
```

Komut hedef veritabanı adının canlı veritabanından farklı olduğunu doğrular ve hedefte tablo varsa işlemi durdurur. Hedef veritabanı önceden oluşturulmuş ve boş olmalıdır. Geri yükleme, Google Drive dosyasının kimliğini kullanır; Drive'daki dosya adı tek başına yeterli değildir.

## Önemli

Bu ilk aşama bağlantı temelidir. Mevcut uygulamada bazı dosyalar public URL, local path ve doğrudan Storage::path() ile kullanılıyor. Bu yüzden tüm medyayı taşımadan önce her özellik için Drive file ID tabanlı erişim, yetkilendirme, CDN/cache ve resumable upload katmanı eklenmelidir. Büyük dosyalar PHP belleğine bütünüyle alınmamalıdır. Yedekler Drive'a yüklenmeden önce şifrelenmeli ve geri yükleme testi yapılmalıdır.

Access/refresh tokenları Eloquent encrypted casts ile şifrelenir. APP_KEY kaybedilirse tokenlar çözülemez.
