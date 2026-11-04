@extends('layouts.app')

@section('title', 'Kategori düzenle')

@section('content')
    <style>
        .channel-edit .channel-image-upload {
            display: flex !important;
            align-items: center !important;
            gap: 18px !important;
            width: 100%;
            max-width: 620px;
            padding: 16px !important;
            border: 1px solid var(--border-color, #e5e7eb);
            border-radius: 14px;
            background: #fff !important;
            box-sizing: border-box;
        }
        .channel-edit .channel-image-upload__preview {
            display: flex !important;
            flex: 0 0 88px !important;
            width: 88px !important;
            height: 88px !important;
            min-width: 88px;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important;
            border: 1px solid #e5e7eb;
            border-radius: 14px !important;
            background: #f3f6fa;
            color: #64748b;
            box-sizing: border-box;
        }
        .channel-edit .channel-image-upload__preview img {
            display: block !important;
            width: 100% !important;
            height: 100% !important;
            max-width: 100% !important;
            max-height: 100% !important;
            object-fit: cover !important;
        }
        .channel-edit .channel-image-upload__content { flex: 1 1 auto; min-width: 0; }
        .channel-edit .channel-image-upload__content > strong { display:block; font-size: .95rem; }
        .channel-edit .channel-image-upload__content > p { margin: 4px 0 10px; color: var(--color-gray, #64748b); font-size: .82rem; }
        .channel-edit .channel-image-upload__actions { display:flex; align-items:center; flex-wrap:wrap; gap:10px; }
        .channel-edit .channel-image-upload__button {
            display:inline-flex !important; align-items:center; justify-content:center; gap:7px;
            min-height:36px; padding:0 13px; border:1px solid #dbe3ee; border-radius:9px;
            background:#f8fafc; color:#1769ff; cursor:pointer; font-size:.84rem; font-weight:600;
        }
        .channel-edit .channel-image-upload__button:hover { background:#eff6ff; border-color:#1769ff; }
        .channel-edit .channel-image-upload__filename { color:#64748b; font-size:.8rem; overflow-wrap:anywhere; }
        .channel-edit .channel-image-upload__hint { display:block; margin-top:8px; color:#64748b; font-size:.76rem; }
        .channel-edit .channel-image-upload__remove { display:flex; align-items:center; gap:7px; width:fit-content; margin-top:9px; color:#64748b; font-size:.8rem; }
        .channel-edit .channel-image-upload__remove input { width:15px; height:15px; margin:0; accent-color:#1769ff; }
        .channel-edit .channel-image-upload__input { position:absolute !important; width:1px !important; height:1px !important; padding:0 !important; margin:-1px !important; overflow:hidden !important; clip:rect(0,0,0,0) !important; white-space:nowrap !important; border:0 !important; }
        @media (max-width: 640px) {
            .channel-edit .channel-image-upload { align-items:flex-start !important; gap:13px !important; padding:13px !important; }
            .channel-edit .channel-image-upload__preview { flex-basis:68px !important; width:68px !important; height:68px !important; min-width:68px; }
            .channel-edit .channel-image-upload__content > p { margin-bottom:8px; }
        }
    </style>

    <section class="subscriptions__section card card-body channel-edit">
        <a href="{{ route('channels.show', $channel) }}" class="link-accent channel-edit__back">
            <x-repost-icon name="chevron-left" size="16" />
            <span>Kategoriye dön</span>
        </a>

        <div class="channel-edit__heading">
            <div>
                <h1 class="h3">Kanalı düzenle</h1>
                <p>Kanal bilgilerini ve profil görselini buradan güncelleyebilirsin.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="allert" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="setting__box setting__list channel-edit__form" action="{{ route('channels.update', $channel) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <label class="setting__list-item" for="channel-name">
                <span class="setting__list-label">Kategori adı</span>
                <span class="setting__list-value">
                    <input id="channel-name" name="name" type="text" value="{{ old('name', $channel->name) }}" maxlength="255" required>
                </span>
            </label>

            <label class="setting__list-item" for="channel-slug">
                <span class="setting__list-label">URL adresi</span>
                <span class="setting__list-value">
                    <input id="channel-slug" name="slug" type="text" value="{{ old('slug', $channel->slug) }}" maxlength="255">
                    <span class="channel-edit__field-hint"><span class="channel-edit__field-hint-label">URL BİLGİSİ</span>Bu adres, kanalının paylaşılabilir bağlantısında kullanılır. Kısa ve kolay hatırlanır bir adres seç.</span>
                </span>
            </label>

            <div class="setting__list-item channel-edit__image-row">
                <span class="setting__list-label">Kanal profil resmi</span>
                <div class="setting__list-value">
                    <div class="channel-image-upload">
                        <div class="channel-image-upload__preview" id="channel-image-preview">
                            @if ($channel->image_path)
                                <img src="{{ asset('storage/'.$channel->image_path) }}" alt="{{ $channel->name }}" id="channel-image-preview-img">
                            @else
                                <x-repost-icon name="image" size="28" />
                            @endif
                        </div>

                        <div class="channel-image-upload__content">
                            <strong>Profil görseli</strong>
                            <p>Kanalını temsil eden bir görsel yükle. Kare görseller en iyi sonucu verir.</p>

                            <div class="channel-image-upload__actions">
                                <label class="channel-image-upload__button" for="channel-image">
                                    <x-repost-icon name="image" size="16" />
                                    <span>Görsel seç</span>
                                </label>
                                <span class="channel-image-upload__filename" id="channel-image-filename">Henüz dosya seçilmedi</span>
                            </div>

                            <span class="channel-image-upload__hint">JPG, PNG veya WebP · En fazla 5 MB</span>

                            @if ($channel->image_path)
                                <label class="channel-image-upload__remove">
                                    <input type="checkbox" name="remove_image" value="1" id="channel-remove-image">
                                    <span>Mevcut görseli kaldır</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <input
                        class="channel-image-upload__input"
                        id="channel-image"
                        name="image"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                    >
                </div>
            </div>

            <label class="setting__list-item" for="channel-description">
                <span class="setting__list-label">Açıklama</span>
                <span class="setting__list-value">
                    <textarea id="channel-description" name="description" rows="4" maxlength="2000">{{ old('description', $channel->description) }}</textarea>
                    <span class="channel-edit__field-hint"><span class="channel-edit__field-hint-label">AÇIKLAMA İPUCU</span>Kanalın konusu, kimlere hitap ettiği ve burada ne tür içerikler paylaşılacağını anlat. Ziyaretçiler bu metin sayesinde kanalını tanır.</span>
                </span>
            </label>

            <div class="subscriptions__action channel-edit__actions">
                <button class="btn btn-primary" type="submit">Değişiklikleri kaydet</button>
            </div>
        </form>
    </section>

    <script>
        (() => {
            const input = document.getElementById('channel-image');
            const preview = document.getElementById('channel-image-preview');
            const filename = document.getElementById('channel-image-filename');
            const remove = document.getElementById('channel-remove-image');

            if (!input || !preview || !filename) return;

            input.addEventListener('change', () => {
                const file = input.files && input.files[0];

                if (!file) {
                    filename.textContent = 'Henüz dosya seçilmedi';
                    return;
                }

                filename.textContent = file.name;

                if (!file.type.startsWith('image/')) return;

                const reader = new FileReader();
                reader.onload = event => {
                    preview.innerHTML = '';
                    const image = document.createElement('img');
                    image.src = event.target.result;
                    image.alt = 'Yeni kanal profil görseli önizlemesi';
                    preview.appendChild(image);
                };
                reader.readAsDataURL(file);

                if (remove) remove.checked = false;
            });
        })();
    </script>
@endsection
