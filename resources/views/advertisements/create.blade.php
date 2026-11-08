@extends('layouts.app')

@section('title', 'Reklam oluştur')

@section('content')
    <header class="feed-heading">
        <h1 class="h3">Reklam oluştur</h1>
        <p class="color-gray">Reklamını hazırla, yayın tarihini ve bütçeni belirle.</p>
    </header>

    @if ($errors->any())
        <div class="card card-body color-red" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form class="card card-body" action="{{ route('advertisements.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="addpost__content">
            <ul class="addpost__fields">
                <li><label class="addpost__label" for="ad-title">Reklam başlığı</label><input class="addpost__title" id="ad-title" name="title" maxlength="160" value="{{ old('title') }}" required></li>
                <li><label class="addpost__label" for="ad-description">Açıklama</label><textarea id="ad-description" name="description" rows="4" maxlength="2000" required>{{ old('description') }}</textarea></li>
                <li><label class="addpost__label" for="ad-image">Reklam görseli</label><input class="form-attachments" id="ad-image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"></li>
                <li><label class="addpost__label" for="ad-video">Video bağlantısı (isteğe bağlı, HTTPS)</label><input id="ad-video" name="video_url" type="url" value="{{ old('video_url') }}" placeholder="https://..."></li>
                <li><label class="addpost__label" for="ad-url">Tıklanınca açılacak bağlantı (HTTPS)</label><input id="ad-url" name="target_url" type="url" value="{{ old('target_url') }}" placeholder="https://..." required></li>
                <li><label class="addpost__label" for="ad-button">Buton yazısı</label><select id="ad-button" name="button_text">@foreach (['Daha fazla bilgi al','İncele','Alışveriş yap','Kaydol','Hemen başvur'] as $label)<option value="{{ $label }}" @selected(old('button_text', 'Daha fazla bilgi al') === $label)>{{ $label }}</option>@endforeach</select></li>
                <li><label class="addpost__label" for="ad-start">Yayın başlangıcı</label><input id="ad-start" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" required></li>
                <li><label class="addpost__label" for="ad-end">Yayın bitişi</label><input id="ad-end" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" required></li>
                <li><label class="addpost__label" for="ad-total">Toplam bütçe (₺)</label><input id="ad-total" name="total_budget" type="number" min="1" step="0.01" value="{{ old('total_budget') }}" required></li>
                <li><label class="addpost__label" for="ad-daily">Günlük bütçe (₺)</label><input id="ad-daily" name="daily_budget" type="number" min="1" step="0.01" value="{{ old('daily_budget') }}" required></li>
            </ul>
            <p class="small color-gray">Reklamlar yönetici onayına gönderilir. Ücretli reklamlar ödeme durumu yönetici tarafından doğrulanana kadar yayınlanmaz. Bu sürümde çevrimiçi ödeme altyapısı henüz bağlı değildir.</p>
            <footer class="addpost__footer">
                <button class="btn btn-primary" type="submit">Reklam başvurusu gönder</button>
                <a class="btn btn-secondary-ghost" href="{{ route('advertisements.index') }}">Vazgeç</a>
            </footer>
        </div>
    </form>
@endsection
