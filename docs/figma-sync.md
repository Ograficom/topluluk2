# Figma -> GitHub senkronizasyonu

Bu entegrasyon Figma dosyasındaki değişiklikleri izler, değişiklik snapshot'ını GitHub'a taşır, Vite/Tailwind build kontrolünü çalıştırır ve değişikliği doğrudan `main` dalına basmak yerine ayrı bir pull request içinde tutar.

## Akış

1. Figma `FILE_UPDATE` veya `FILE_VERSION_UPDATE` olayı `POST /api/integrations/figma/webhook` adresine gelir.
2. Laravel, webhook `passcode` değerini ve `file_key` değerini doğrular.
3. Laravel GitHub'a `repository_dispatch` olayı gönderir.
4. `.github/workflows/figma-sync.yml` çalışır.
5. `scripts/figma-sync.mjs` Figma REST API'den dosyayı okur ve fingerprint üretir.
6. Değişiklik varsa `.figma-sync/state.json` ve `.figma-sync/design-index.json` güncellenir.
7. `npm ci` ve `npm run build` başarılı olmak zorundadır.
8. Değişiklik `automation/figma-sync` dalına gönderilir ve PR açılır/güncellenir.

GitHub Actions ayrıca webhook gecikmesine karşı her 5 dakikada bir yedek kontrol yapar.

## Gerekli sunucu ortam değişkenleri

```env
FIGMA_FILE_KEY=
FIGMA_ACCESS_TOKEN=
FIGMA_WEBHOOK_PASSCODE=
FIGMA_SYNC_GITHUB_REPOSITORY=Ograficom/topluluk2
FIGMA_SYNC_GITHUB_TOKEN=
FIGMA_SYNC_GITHUB_EVENT=figma_update
```

`FIGMA_SYNC_GITHUB_TOKEN` yalnızca ilgili repository için `repository_dispatch` gönderecek kadar yetkili tutulmalıdır.

## Gerekli GitHub Actions secrets

Repository Settings -> Secrets and variables -> Actions altında:

- `FIGMA_FILE_KEY`
- `FIGMA_ACCESS_TOKEN`

Bu değerler workflow loglarına yazdırılmaz.

## Figma webhook

Figma REST API v2 webhook kaydı dosya bağlamında yapılır:

- event type: `FILE_UPDATE`
- context: `file`
- context id: `FIGMA_FILE_KEY`
- endpoint: `https://<site-adresi>/api/integrations/figma/webhook`
- passcode: sunucudaki `FIGMA_WEBHOOK_PASSCODE` ile aynı değer

Figma webhook tarafında `FILE_UPDATE` gerçek zamanlı her hareket için gönderilmez. Bu nedenle workflow içinde 5 dakikalık polling yedeği vardır.

## Tasarım -> Blade/Tailwind eşlemesi

Snapshot mekanizması değişikliği güvenilir biçimde algılar; fakat Figma'daki rastgele bir layer değişikliğini güvenli şekilde otomatik Blade koduna çevirmek için component eşleme katmanı gerekir. Bu katman Figma dosyasındaki gerçek node/component kimlikleri görüldükten sonra eklenmelidir.

Önerilen eşleme biçimi:

```text
Figma Button component -> resources/views/components/button.blade.php
Figma Post Card       -> resources/views/components/post-card.blade.php
Figma Avatar          -> resources/views/components/avatar.blade.php
Figma design tokens   -> resources/css/app.css / Tailwind theme
```

Böylece bir component değiştiğinde tüm sayfalar merkezi component üzerinden güncellenir; her ekranın HTML'i yeniden üretilmez.
