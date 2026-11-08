@extends('layouts.app')

@section('title', 'Reklamı düzenle')

@section('content')
    <header class="feed-heading">
        <h1 class="h3">Reklamı düzenle</h1>
        <p class="color-gray">İçeriği güncelle. Kaydettiğinde reklamın yeniden yönetici onayına gönderilir.</p>
    </header>

    @if ($errors->any())
        <div class="card card-body color-red" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form class="card card-body" action="{{ route('advertisements.update', $advertisement) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="addpost__content">
            <ul class="addpost__fields">
                <li>
                    <label class="addpost__label" for="ad-title">Reklam başlığı</label>
                    <input class="addpost__title" id="ad-title" name="title" maxlength="160" value="{{ old('title', $advertisement->title) }}" required>
                </li>
                <li>
                    <label class="addpost__label" for="ad-description">Açıklama</label>
                    <textarea id="ad-description" name="description" rows="7" maxlength="2000" required>{{ old('description', $advertisement->description) }}</textarea>
                </li>
                <li>
                    <label class="addpost__label" for="ad-image">Yeni reklam görseli</label>
                    @if ($advertisement->imageUrl())
                        <div class="mb-2"><img src="{{ $advertisement->imageUrl() }}" alt="{{ $advertisement->title }}" style="display:block;max-width:240px;max-height:140px;object-fit:contain;border-radius:10px"></div>
                        <label class="small"><input type="checkbox" name="remove_image" value="1"> Mevcut görseli kaldır</label>
                    @endif
                    <input class="form-attachments" id="ad-image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                </li>
                <li>
                    <label class="addpost__label" for="ad-video">Video bağlantısı (isteğe bağlı, HTTPS)</label>
                    <input id="ad-video" name="video_url" type="url" value="{{ old('video_url', $advertisement->video_url) }}" placeholder="https://...">
                </li>
                <li>
                    <label class="addpost__label" for="ad-url">Harici bağlantı (isteğe bağlı, HTTPS)</label>
                    <input id="ad-url" name="target_url" type="url" value="{{ old('target_url', $advertisement->target_url) }}" placeholder="Boş bırakılırsa ayrıntı sayfası gösterilir">
                </li>
                <li>
                    <label class="addpost__label" for="ad-button">Buton yazısı</label>
                    <select id="ad-button" name="button_text">
                        @foreach (['Daha fazla bilgi al','İncele','Alışveriş yap','Kaydol','Hemen başvur'] as $label)
                            <option value="{{ $label }}" @selected(old('button_text', $advertisement->button_text) === $label)>{{ $label }}</option>
                        @endforeach
                    </select>
                </li>
                <li>
                    <label class="addpost__label" for="ad-start">Yayın başlangıcı</label>
                    <input id="ad-start" name="starts_at" type="datetime-local" value="{{ old('starts_at', $advertisement->starts_at?->timezone('Europe/Istanbul')->format('Y-m-d\TH:i')) }}" required>
                </li>
                <li>
                    <label class="addpost__label" for="ad-end">Yayın bitişi</label>
                    <input id="ad-end" name="ends_at" type="datetime-local" value="{{ old('ends_at', $advertisement->ends_at?->timezone('Europe/Istanbul')->format('Y-m-d\TH:i')) }}" required>
                </li>
            </ul>
            <p class="small color-gray">Günlük ücret: <strong>{{ number_format($dailyPrice, 2, ',', '.') }} ₺</strong>. Yeni yayın süresine göre toplam bütçe yeniden hesaplanır. Kaydettiğinde reklam tekrar onaya alınır.</p>
            <footer class="addpost__footer">
                <button class="btn btn-primary" type="submit">Değişiklikleri kaydet</button>
                <a class="btn btn-secondary-ghost" href="{{ route('advertisements.index') }}">Vazgeç</a>
            </footer>
        </div>
    </form>
@endsection
