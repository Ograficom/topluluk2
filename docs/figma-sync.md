# Figma -> GitHub senkronizasyonu

Bu entegrasyon `Ografi iOS UI Kit — 2026` Figma dosyasını (`MCDxzKsjWhMshCAhliWEjL`) `Ograficom/topluluk2` Laravel projesindeki ortak web design-system katmanına bağlar. Değişiklikler doğrudan `main` dalına basılmaz; build kontrolünden sonra ayrı bir pull request içinde tutulur.

## Akış

1. Figma `FILE_UPDATE` veya `FILE_VERSION_UPDATE` olayı `POST /api/integrations/figma/webhook` adresine gelir.
2. Laravel, webhook `passcode` değerini ve `file_key` değerini doğrular.
3. Laravel GitHub'a `repository_dispatch` olayı gönderir.
4. `.github/workflows/figma-sync.yml` çalışır. Ayrıca 5 dakikada bir polling yedeği vardır.
5. `scripts/figma-sync.mjs` Figma REST API'den dosyayı ve `design/figma-map.json` içindeki gerçek node ID'lerini okur.
6. Renk, tipografi, spacing, radius ve eşlenmiş component ölçüleri `public/css/figma.generated.css` dosyasına dönüştürülür.
7. `.figma-sync/state.json` ve `.figma-sync/design-index.json` güncellenir.
8. `npm ci` ve `npm run build` başarılı olmak zorundadır.
9. Değişiklik `automation/figma-sync` dalına gönderilir ve PR açılır/güncellenir.

## Figma dosyası

```text
File key: MCDxzKsjWhMshCAhliWEjL
Pages:
00 Cover       -> 0:1
01 Foundations -> 1:47
02 Components  -> 1:48
03 Screens     -> 1:49
```

`config/figma.php` bu file key'i varsayılan olarak içerir. İstenirse `FIGMA_FILE_KEY` ile override edilebilir.

## Gerçek design-system eşlemesi

Kaynak eşleme dosyası: `design/figma-map.json`.

### Foundations

```text
Colors      -> 1:54, 1:57, 1:60, 1:63, 1:66, 1:69, 1:72, 1:75
Typography  -> 1:80, 1:83, 1:86, 1:89, 1:92, 1:95, 1:98
Spacing     -> 1:104, 1:107, 1:110, 1:113, 1:116, 1:119, 1:122
Radius      -> 1:126, 1:129, 1:132, 1:135, 1:138, 1:141
```

### Components

```text
1:143 Button/Primary       -> resources/views/components/button.blade.php
1:145 Chip/Default         -> generated CSS tokenları
1:147 Search Field/Default -> generated CSS tokenları / web adaptation
1:150 Avatar/Default       -> post-card avatar sistemi
1:151 Post Card/Default    -> resources/views/blog/post-card.blade.php
1:163 Video Card/Default   -> generated CSS tokenları / web adaptation
1:168 Tab Bar/Default      -> iOS-only, web'e zorla taşınmaz
1:349 Icon Button/Default  -> button sistemi / web adaptation
1:351 Bottom Sheet/Default -> modal sistemi / web adaptation
```

Figma'daki `Button/Primary` iOS'ta 48pt ana aksiyon düğmesidir. Web tarafında yalnızca `variant="primary"` + `size="lg"` Blade düğmeleri bu node'a bağlanır; küçük utility düğmeleri iOS ölçüsüne zorlanmaz.

`Post Card/Default` Figma'daki ortak renk, border ve radius değerlerini mevcut dinamik `blog.post-card` yapısına uygular. Web'deki yorum, reaksiyon, medya ve menü davranışları korunur; iOS örnek metni veya sabit 343x240 ölçüsü dinamik web içeriğine zorla uygulanmaz.

## Üretilen CSS

`public/css/figma.generated.css` otomatik üretilir ve `resources/views/partials/font-assets.blade.php` üzerinden tüm Laravel sayfalarında yüklenir.

Örnek değişkenler:

```css
--figma-color-action-primary
--figma-color-border-default
--figma-space-lg
--figma-radius-lg
--figma-button-primary-height
--figma-button-primary-radius
--figma-post-card-default-border-color
--figma-post-card-default-radius
```

Eşlenmiş bir Figma node'unda fill, radius, padding, font boyutu/ağırlığı veya benzeri desteklenen özellik değişirse sonraki senkron çalışmasında CSS yeniden üretilir ve PR'a dahil edilir.

## Gerekli GitHub Actions secret

Repository Settings -> Secrets and variables -> Actions altında:

```text
FIGMA_ACCESS_TOKEN
```

`FIGMA_FILE_KEY` workflow içinde bu proje için sabitlenmiştir ve gizli değildir.

## Gerekli sunucu ortam değişkenleri

Webhook kullanılacaksa sunucuda:

```env
FIGMA_FILE_KEY=MCDxzKsjWhMshCAhliWEjL
FIGMA_WEBHOOK_PASSCODE=
FIGMA_SYNC_GITHUB_REPOSITORY=Ograficom/topluluk2
FIGMA_SYNC_GITHUB_TOKEN=
FIGMA_SYNC_GITHUB_EVENT=figma_update
```

`FIGMA_SYNC_GITHUB_TOKEN` yalnızca ilgili repository için `repository_dispatch` gönderecek kadar yetkili tutulmalıdır. Polling tek başına kullanılacaksa Laravel webhook değişkenleri zorunlu değildir.

## Figma webhook

Webhook kaydı yapılırsa:

- event type: `FILE_UPDATE`
- context: `file`
- context id: `MCDxzKsjWhMshCAhliWEjL`
- endpoint: `https://<site-adresi>/api/integrations/figma/webhook`
- passcode: sunucudaki `FIGMA_WEBHOOK_PASSCODE` ile aynı değer

## Code Connect notu

Bu Figma hesabı Professional planda olduğu için resmi Figma Code Connect kullanılamıyor. Bu nedenle repo tarafında `design/figma-map.json` ile deterministik node -> Blade/CSS eşlemesi kullanılıyor. Organization veya Enterprise plana geçilirse aynı node ID'leri resmi Code Connect eşlemelerine taşınabilir.

## Bilinçli sınır

Bu sistem rastgele Figma metnini veritabanındaki içerikle değiştirmez ve iOS'a özgü Tab Bar gibi öğeleri web'e körlemesine kopyalamaz. Tasarım sistemi özellikleri otomatik senkronlanır; uygulama davranışı ve dinamik Laravel verisi kodun kontrolünde kalır.
