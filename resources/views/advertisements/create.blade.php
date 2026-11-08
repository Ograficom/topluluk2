@extends('layouts.app')

@php($editing = isset($advertisement))

@section('title', $editing ? 'Reklamı düzenle' : 'Reklam oluştur')

@section('content')
    <header class="feed-heading">
        <h1 class="h3">{{ $editing ? 'Reklamı düzenle' : 'Reklam oluştur' }}</h1>
        <p class="color-gray">{{ $editing ? 'Reklam içeriğini, görselini ve yayın tarihlerini güncelle.' : 'Reklamını hazırla, yayın süresini seç; ücret otomatik hesaplansın.' }}</p>
    </header>

    @if ($errors->any())
        <div class="card card-body color-red" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form class="card card-body" action="{{ $editing ? route('advertisements.update', $advertisement) : route('advertisements.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if ($editing) @method('PATCH') @endif
        <div class="addpost__content">
            <ul class="addpost__fields">
                <li><label class="addpost__label" for="ad-title">Reklam başlığı</label><input class="addpost__title" id="ad-title" name="title" maxlength="160" value="{{ old('title', $advertisement->title ?? '') }}" required></li>
                <li><label class="addpost__label" for="ad-description">Açıklama</label><textarea id="ad-description" name="description" rows="4" maxlength="2000" required>{{ old('description', $advertisement->description ?? '') }}</textarea></li>
                <li><label class="addpost__label" for="ad-image">Reklam görseli</label><input class="form-attachments" id="ad-image" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                    @if ($editing && $advertisement->imageUrl())<div class="small color-gray mt-2"><img src="{{ $advertisement->imageUrl() }}" alt="Mevcut reklam görseli" style="display:block;max-width:220px;max-height:130px;object-fit:cover;border-radius:10px;margin-bottom:8px"><label><input type="checkbox" name="remove_image" value="1"> Mevcut görseli kaldır</label></div>@endif</li>
                <li><label class="addpost__label" for="ad-video">Video bağlantısı (isteğe bağlı, HTTPS)</label><input id="ad-video" name="video_url" type="url" value="{{ old('video_url', $advertisement->video_url ?? '') }}" placeholder="https://..."></li>
                <li><label class="addpost__label" for="ad-url">Harici bağlantı (isteğe bağlı, HTTPS)</label><input id="ad-url" name="target_url" type="url" value="{{ old('target_url', $advertisement->target_url ?? '') }}" placeholder="Boş bırakılırsa ayrıntı sayfası gösterilir"></li>
                <li><label class="addpost__label" for="ad-button">Harici bağlantı buton yazısı</label><select id="ad-button" name="button_text">@foreach (['Daha fazla bilgi al','İncele','Alışveriş yap','Kaydol','Hemen başvur'] as $label)<option value="{{ $label }}" @selected(old('button_text', $advertisement->button_text ?? 'Daha fazla bilgi al') === $label)>{{ $label }}</option>@endforeach</select></li>
                <li><label class="addpost__label" for="ad-start">Yayın başlangıcı</label><input id="ad-start" name="starts_at" type="datetime-local" value="{{ old('starts_at', isset($advertisement) ? $advertisement->starts_at->timezone('Europe/Istanbul')->format('Y-m-d\\TH:i') : '') }}" required></li>
                <li><label class="addpost__label" for="ad-end">Yayın bitişi</label><input id="ad-end" name="ends_at" type="datetime-local" value="{{ old('ends_at', isset($advertisement) ? $advertisement->ends_at->timezone('Europe/Istanbul')->format('Y-m-d\\TH:i') : '') }}" required></li>
                <li>
                    <label class="addpost__label" for="ad-duration">Yayın süresi</label>
                    <input id="ad-duration" type="text" value="Tarihleri seçin" readonly aria-live="polite">
                </li>
                <li>
                    <label class="addpost__label" for="ad-total">Toplam reklam ücreti (₺)</label>
                    <input id="ad-total" type="text" value="Tarihleri seçin" readonly aria-live="polite">
                    <input id="ad-total-value" type="hidden" name="total_budget" value="">
                    <input type="hidden" name="daily_budget" value="{{ number_format($dailyPrice, 2, '.', '') }}">
                    <p class="small color-gray">Günlük reklam ücreti: <strong>{{ number_format($dailyPrice, 2, ',', '.') }} ₺</strong>. Toplam tutar seçtiğin gün sayısına göre hesaplanır.</p>
                </li>
            </ul>
            <p class="small color-gray">Reklamlar yönetici onayına gönderilir. Ücretli reklamlar ödeme durumu yönetici tarafından doğrulanana kadar yayınlanmaz. Bu sürümde çevrimiçi ödeme altyapısı henüz bağlı değildir.</p>
            <footer class="addpost__footer">
                <button class="btn btn-primary" type="submit">{{ $editing ? 'Değişiklikleri kaydet' : 'Reklam başvurusu gönder' }}</button>
                <a class="btn btn-secondary-ghost" href="{{ route('advertisements.index') }}">Vazgeç</a>
            </footer>
        </div>
    </form>

    <script>
        (() => {
            const start = document.getElementById('ad-start');
            const end = document.getElementById('ad-end');
            const duration = document.getElementById('ad-duration');
            const total = document.getElementById('ad-total');
            const totalValue = document.getElementById('ad-total-value');
            const dailyPrice = {{ json_encode((float) $dailyPrice) }};
            const money = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', maximumFractionDigits: 2 });

            const calculate = () => {
                if (!start.value || !end.value) {
                    duration.value = 'Tarihleri seçin';
                    total.value = 'Tarihleri seçin';
                    totalValue.value = '';
                    return;
                }

                const startDate = new Date(start.value);
                const endDate = new Date(end.value);
                const difference = endDate.getTime() - startDate.getTime();

                if (!Number.isFinite(difference) || difference <= 0) {
                    duration.value = 'Geçerli bir süre seçin';
                    total.value = '—';
                    totalValue.value = '';
                    return;
                }

                const days = Math.max(1, Math.ceil(difference / 86400000));
                const amount = Math.round(days * dailyPrice * 100) / 100;

                duration.value = days + (days === 1 ? ' gün' : ' gün');
                total.value = money.format(amount);
                totalValue.value = amount.toFixed(2);
            };

            start.addEventListener('change', calculate);
            end.addEventListener('change', calculate);
            calculate();
        })();
    </script>
@endsection
