# OgraFi

OgraFi, kullanıcıların içerik yayımlayabildiği, farklı kanallarda topluluklar oluşturabildiği ve diğer üyelerle etkileşim kurabildiği Laravel tabanlı bir içerik ve topluluk platformudur.

Proje; içerik akışı, kanal sayfaları, üye profilleri ve yönetim araçlarını tek bir uygulamada bir araya getirir. Arayüz, masaüstü ve mobil ekranlarda kullanılabilecek şekilde tasarlanmıştır.

## Öne çıkan özellikler

### İçerik ve akış
- Yayınlanmış yazıların listelendiği ana akış.
- Yazı ayrıntı sayfaları, arama ve sayfalama.
- Yazılarda kapak/görsel galerisi, etiketler ve içerik bilgileri.
- Beğeni, beğenmeme, yorum, görüntülenme ve yer imi etkileşimleri.
- Yazı oluşturma ve yayın durumunu yönetme.

### Kanallar ve topluluk
- Kanalların listelendiği keşif sayfası.
- Her kanal için açıklama, abone sayısı ve kanal yazıları.
- Kanallara abone olma ve kanal içeriklerini görüntüleme.
- Kanal yazıları ve yorumları arasında gezinme.

### Üyelik ve profiller
- Kayıt, oturum açma ve e-posta doğrulama.
- Kullanıcı adıyla profil adresleri.
- Profil bilgileri, avatar ve sosyal bağlantılar.
- Kullanıcıları takip etme ve çevrimiçi durumunu gösterme.
- Google ve Yandex gibi sosyal giriş sağlayıcılarıyla hesap bağlantısı.
- Bildirim ve hesap tercihleri.

### Yönetim paneli
- Filament tabanlı yönetim paneli: `/admin`.
- Yazı ve kanal yönetimi.
- Kullanıcı ve doğrulama başvurularını yönetme.
- Marka, e-posta, sitemap ve RSS ayarları.
- Yönetim paneline erişim yalnızca yönetici yetkisine sahip hesaplara açıktır.

## Teknoloji altyapısı

- **Backend:** Laravel 12
- **Yönetim paneli:** Filament 5
- **Frontend:** Blade, Tailwind CSS, Vite ve JavaScript
- **Veritabanı:** Laravel tarafından desteklenen veritabanları (sunucu yapılandırmasına bağlı olarak MySQL/MariaDB veya SQLite)
- **PHP:** 8.2 veya üzeri

## Kurulum

Projeyi klonlayın ve bağımlılıkları yükleyin:

```bash
git clone https://github.com/Ograficom/topluluk2.git
cd topluluk2
composer install
npm install
```

Ortam dosyasını oluşturun:

**Windows PowerShell**
```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

**Linux / macOS**
```bash
cp .env.example .env
```

Uygulama anahtarını oluşturun:

```bash
php artisan key:generate
```

Veritabanı bağlantısını `.env` dosyasında yapılandırdıktan sonra:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

Geliştirme sunucusunu başlatın:

```bash
php artisan serve
```

Uygulama varsayılan olarak `http://127.0.0.1:8000` adresinde açılır. Yönetim paneli `/admin` yolundadır.

## Yönetici hesabı

İlk yönetici hesabının oluşturulması için `.env` dosyasındaki aşağıdaki değerleri yapılandırın:

```dotenv
FILAMENT_ADMIN_EMAIL=admin@example.com
FILAMENT_ADMIN_PASSWORD=guclu-bir-parola
```

Ardından veritabanı seed işlemini çalıştırın:

```bash
php artisan migrate --seed
```

Mevcut bir kullanıcıya yönetici yetkisi vermek için komutu uygulamanın çalıştığı ortamda çalıştırın:

```bash
php artisan admin:grant kullanici@example.com
```

Komut kullanıcı hesabını doğrular ve yönetici yetkisi vermeden önce onay ister. GitHub'a kod göndermek, uzak sunucudaki kullanıcı yetkilerini otomatik olarak değiştirmez; komut dağıtım yapılan sunucuda ayrıca çalıştırılmalıdır.

## Testler

```bash
php artisan test
```

## Proje yapısı

- `app/` — Laravel uygulama kodu, modeller ve yönetim paneli sınıfları.
- `database/` — Migration, factory ve seed dosyaları.
- `resources/` — Blade görünümleri, CSS ve JavaScript kaynakları.
- `public/` — Genel erişime açık varlıklar ve derlenmiş dosyalar.
- `routes/` — Web ve uygulama rotaları.

## Lisans

Bu depoda ayrıca bir lisans dosyası belirtilmediği sürece kullanım ve dağıtım koşulları için depo sahibinden izin alınmalıdır.
