# ⚡ SEO HIZLI REFERANS REHBERI

## 📌 KRITIK SEO DOSYALARI

```
📁 resources/views/blog/
  ├── show.blade.php (8700+ satır - ANA POST SHOW SAYFASı)
  │   └── 🔥 Meta Tags (8708-8750)
  │   └── 🔥 JSON-LD (1050-1350)
  │
  ├── partials/post-comments.blade.php
  │   └── Yorum Schema
  │
  ├── index.blade.php
  │   └── Kategori & Breadcrumb
  │
  └── post-card.blade.php
      └── Card Component

📁 resources/views/layouts/
  └── app.blade.php
      └── Global Meta Tags (20-50 satırları)

📁 resources/views/
  └── home.blade.php
      └── Ana Sayfa (Meta açıklaması eklenebilir)
```

---

## 🔧 HIZLI BAKIŞIM

### Meta Tags Kontrolü

```blade
<!-- YAPILMASI GEREKEN -->
@section('title', 'Sayfa Başlığı')
@section('meta_description', 'Kısa açıklama - 155 karakter')
@section('canonical_url', route('page.route'))

<!-- YAPILMAMASI GEREKEN -->
@section('title', 'Very Very Very Very Very Long Title That Is Too Long For Search Results')
<!-- Meta description çok kısa veya hiç olmamak -->
```

### Görüntü Meta Tags Kontrolü

```blade
<!-- DOĞRU -->
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="675">
<meta property="og:image:alt" content="Açıklamalı alt text">

<!-- YANLIŞ -->
<meta property="og:image" content="/image.jpg">
<!-- width, height, alt eksik -->
```

### Schema Kontrolü

```blade
<!-- Sayfanın en altında kontrol et -->
<script type="application/ld+json">
{!! json_encode($seoJsonLdGraph) !!}
</script>

<!-- YANLIŞ - Raw HTML escape olmaksızın -->
<script type="application/ld+json">
{{ $seoJsonLdGraph }}
</script>
```

---

## 📋 KONTROL LİSTESİ

### Her Sayfada Olması Gereken

- [ ] `<title>` tag (50-60 karakter)
- [ ] `<meta name="description">` (150-160 karakter)
- [ ] `<link rel="canonical">` 
- [ ] `<meta property="og:type">`
- [ ] `<meta property="og:title">`
- [ ] `<meta property="og:description">`
- [ ] `<meta property="og:image">` (eğer görüntü varsa)
- [ ] `<meta property="og:url">`
- [ ] `<meta name="twitter:card">`
- [ ] `<link rel="alternate" hreflang="...">` (multi-lang ise)

### Post/Makale Sayfasında Olması Gereken

- [ ] `<meta property="article:published_time">` (ISO 8601)
- [ ] `<meta property="article:modified_time">` (ISO 8601)
- [ ] `<meta property="article:author">`
- [ ] `<meta property="article:section">` (kategori)
- [ ] `<meta property="article:tag">` (1+ etiket)
- [ ] NewsArticle JSON-LD
- [ ] DiscussionForum JSON-LD (yorumlar için)
- [ ] BreadcrumbList JSON-LD

### Sosyal Medya Paylaşımı için

- [ ] Title: 50-60 karakter, çekici
- [ ] Description: 155-165 karakter, motivasyon
- [ ] Image: 1200x675 px, yüksek kalite
- [ ] No special chars: ✅ HTML escaped

---

## 🛠️ COMMON TASKS

### Task 1: Yeni Bir Sayfa Eklemek

```blade
<!-- resources/views/my-page.blade.php -->

@extends('layouts.app')

@section('title', 'Sayfa Başlığı | ' . config('app.name'))
@section('meta_description', 'Kısa açıklama maksimum 155 karakter')
@section('canonical_url', route('page.route'))

@push('head')
<meta property="og:type" content="website">
<meta property="og:image" content="{{ asset('image.jpg') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="675">
<meta property="og:image:alt" content="Görüntü açıklaması">

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => 'Sayfa Başlığı',
    'description' => 'Açıklama...',
    'url' => route('page.route'),
]) !!}
</script>
@endpush

@section('content')
    <!-- İçerik -->
@endsection
```

### Task 2: Meta Açıklaması Güncellemek

```blade
<!-- ESKI -->
@section('title', 'Blog')

<!-- YENİ -->
@section('title', 'Blog - En Son Makaleler')
@section('meta_description', 'Ografi Blog - Teknoloji, SEO ve dijital pazarlama hakkında en son makaleler.')
```

### Task 3: Görüntü SEO'su Kontrol Etmek

```php
// Dosya: app/Http/Controllers/BlogController.php

// YAPILMASI GEREKEN
$post = Post::find($id);
$image = $post->featured_image_url;
if ($image) {
    // Görüntü boyutlarını kontrol et
    $size = getimagesize($image);
    if ($size[0] >= 1200 && $size[1] >= 675) {
        // OK - SEO için yeterli
    }
}

// YAPILMAMASI GEREKEN
<img src="{{ $image }}" />
<!-- alt text olmaksızın -->
```

### Task 4: Schema Validation

```bash
# Terminal'de
curl https://validator.schema.org/

# Veya tarayıcıda
# https://validator.schema.org/
# URL yapıştır: https://yoursite.com/post-slug
```

---

## 📊 KPI TRACKING

### Aylık Kontrol Listesi

**1. Google Search Console**
- [ ] Total Clicks: ↑ (Önceki aya göre)
- [ ] Average Position: ↓ (Daha üstte olsun)
- [ ] Impressions: ↑ (Görünürlük)
- [ ] CTR: ↑ (Click-through Rate)
- [ ] Mobile: ✅ (Sorun yok)
- [ ] Core Web Vitals: ✅ (Good)

**2. Analytics**
- [ ] Organic Traffic: ↑ 10%+
- [ ] Average Session Duration: ↑
- [ ] Pages/Session: ↑
- [ ] Bounce Rate: ↓ (Daha düşük)

**3. Rankings**
- [ ] Top 100: +5 keywords
- [ ] Top 50: +2 keywords
- [ ] Top 10: +1 keyword

---

## ⚠️ YAYGÜN HATALAR

### Hata 1: Meta Description Eksikliği

```blade
<!-- ❌ YANLIŞ -->
@section('title', 'Blog Post')
<!-- meta_description: YOK -->

<!-- ✅ DOĞRU -->
@section('title', 'Blog Post')
@section('meta_description', 'Açıklama...')
```

### Hata 2: HTML Escape Olmaksızın Output

```blade
<!-- ❌ YANLIŞ - XSS riski -->
<meta name="description" content="{{ $description }}">

<!-- ✅ DOĞRU - HTML escaped -->
<meta name="description" content="{{ e($description) }}">
```

### Hata 3: Yanlış Görüntü Boyutları

```blade
<!-- ❌ YANLIŞ - Çok küçük -->
<meta property="og:image" content="{{ asset('small-image.jpg') }}"> <!-- 400x300 -->

<!-- ✅ DOĞRU - Optimal -->
<meta property="og:image" content="{{ asset('featured.jpg') }}"> <!-- 1200x675 -->
```

### Hata 4: Duplicate Content

```blade
<!-- ❌ YANLIŞ - Canonical olmaksızın -->
<!-- Sayfa A -->
<title>Blog Post</title>
<!-- Sayfa B -->
<title>Blog Post</title>

<!-- ✅ DOĞRU - Canonical ile -->
<!-- Sayfa A -->
<link rel="canonical" href="https://site.com/post-a">
<!-- Sayfa B -->
<link rel="canonical" href="https://site.com/post-b">
```

### Hata 5: ISO 8601 Olmaksızın Tarih

```blade
<!-- ❌ YANLIŞ -->
<meta property="article:published_time" content="12 June 2026">

<!-- ✅ DOĞRU -->
<meta property="article:published_time" content="2026-06-12T10:30:00+00:00">
```

---

## 🔐 GÜVENLİK KONTROL LİSTESİ

- [ ] Tüm `$variable` çıktılar `e()` ile escape edilmiş
- [ ] JSON için `json_encode()` kullanılmış
- [ ] SQL injection koruması (parametrized queries)
- [ ] CSRF token meta tag: ✅
- [ ] HTTPS: ✅
- [ ] robots.txt: ✅
- [ ] sitemap.xml: ✅

---

## 🚀 OPTIMIZATION TIPS

### Performance

```php
// YAPILMASI GEREKEN - Lazy loading
<img src="image.jpg" loading="lazy" decoding="async" alt="Açıklama">

// YAPILMAMASI GEREKEN - Blocking
<img src="image.jpg" alt="Açıklama">
```

### Cache Strategy

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'file'),

// Schema cachelemeyi düşün (regenerate her 24 saatte)
Cache::remember('seo_schema_' . $post->id, 86400, function () {
    return $post->generateJsonLdSchema();
});
```

---

## 📞 REFERENCE LINKS

| Kaynak | Link | Notlar |
|--------|------|--------|
| Google SEO | https://developers.google.com/search | Official |
| Schema.org | https://schema.org | Validator at validator.schema.org |
| OG Tags | https://ogp.me | Open Graph Spec |
| Twitter | https://developer.twitter.com/en/docs/twitter-for-websites/cards | Twitter Cards |
| JSON-LD | https://json-ld.org | Format |
| Best Practices | https://support.google.com/webmasters | Google Guide |

---

## 📱 MOBILE SEO

```blade
<!-- YAPILMASI GEREKEN -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="apple-touch-icon" href="icon.png">

<!-- YAPILMAMASI GEREKEN -->
<meta name="viewport" content="width=1024"> <!-- Fixed width -->
```

---

## 🎯 SONRAKI 30 GÜN PLAN

```
HAFTA 1:
  [ ] Google Search Console kurulum
  [ ] Sitemap gönderimi
  [ ] İlk indexleme beklemesi

HAFTA 2:
  [ ] Rich Results Test'te tüm sayfalar
  [ ] PageSpeed Insights check
  [ ] Core Web Vitals analiz

HAFTA 3:
  [ ] Analytics data collection
  [ ] Ranking tracking başlatma
  [ ] Content gap analysis

HAFTA 4:
  [ ] İlk raporlama
  [ ] Fine-tuning
  [ ] Next steps planning
```

---

## ✅ BAŞARILI SAYILACAK METRIKLER

```
Hedef (6 Ay):
├── Organik Trafik: +150%
├── Average Position: < 20
├── CTR: > 2.5%
├── Pages/Session: > 2.5
├── Bounce Rate: < 50%
└── Conversions: +50%

RED FLAGS (Kontrol Et):
├── Impressions: Sabit veya ↓
├── Average Position: Sabit veya ↑ (kötü)
├── Core Web Vitals: Failed
├── 404 Errors: > 50
├── Crawl Errors: > 10
└── Mobile Usability: Failed
```

---

## 🆘 TROUBLESHOOTING

### Sorun: Sayfalar İndexe Alınmıyor

```
Kontrol Et:
1. robots.txt - bloklama yok mu?
2. Meta robots - "noindex" yok mu?
3. Canonical - kendine yöneliyor mu?
4. Sitemap - sayfalar eklendi mi?
5. GSC Coverage - hata nedir?

Çözüm:
1. GSC'de "Request Indexing" kullan
2. Sitemap resubmit et
3. Internal links ekle
```

### Sorun: Ranking Düşüş

```
Kontrol Et:
1. Core Web Vitals - kötü leşti mi?
2. Unique Content - plagiarism yok mu?
3. Backlinks - spam links aldı mı?
4. Technical Issues - error oluştu mu?

Çözüm:
1. Performance optimize et
2. Content quality iyileştir
3. Spam links disavow et
4. Crawl issues fix et
```

---

**Son Günceleme:** 12 Haziran 2026  
**Version:** 1.0  
**Status:** ✅ Aktif Kullanımda
