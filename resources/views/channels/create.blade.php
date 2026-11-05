@extends('layouts.app')

@section('title', 'Kategori oluştur')

@section('content')
    <section class="subscriptions__section card card-body">
        <a href="{{ route('channels.index') }}" class="link-accent">← Kategori listesine dön</a>
        <h1 class="h3">Kategori oluştur</h1>

        @if ($errors->any())
            <div class="allert" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="setting__box setting__list channel-edit__form" action="{{ route('channels.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label class="setting__list-item" for="channel-name">
                <span class="setting__list-label">Kategori adı</span>
                <span class="setting__list-value">
                    <input id="channel-name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required>
                </span>
            </label>
            <label class="setting__list-item" for="channel-slug">
                <span class="setting__list-label">URL adresi (isteğe bağlı)</span>
                <span class="setting__list-value">
                    <input id="channel-slug" name="slug" type="text" value="{{ old('slug') }}" maxlength="255" placeholder="Otomatik oluşturulur">
                </span>
            </label>
            <div class="setting__list-item channel-edit__image-row">
                <span class="setting__list-label">Kategori profil resmi</span>
                <div class="setting__list-value">
                    <div class="channel-image-upload">
                        <div class="channel-image-upload__preview-wrap">
                            <div class="channel-image-upload__preview" id="channel-image-preview">
                                <x-repost-icon name="image" size="30" />
                            </div>
                            <span class="channel-image-upload__status">Önizleme</span>
                        </div>

                        <div class="channel-image-upload__content">
                            <strong>Kategori görseli</strong>
                            <span class="channel-image-upload__hint">JPG, PNG veya WebP · En fazla 5 MB</span>

                            <div class="channel-image-upload__actions">
                                <label class="channel-image-upload__button" for="channel-image">
                                    <x-repost-icon name="upload" size="16" />
                                    <span>Görsel seç</span>
                                </label>
                                <span class="channel-image-upload__filename" id="channel-image-filename">Henüz dosya seçilmedi</span>
                            </div>
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
                    <textarea id="channel-description" name="description" rows="4" maxlength="2000" placeholder="Bu kategoride hangi içerikler paylaşılabilir?">{{ old('description') }}</textarea>
                </span>
            </label>
            <div class="subscriptions__action">
                <button class="btn btn-primary" type="submit">Kategori oluştur</button>
            </div>
        </form>
    </section>


    <script>
        (() => {
            const input = document.getElementById('channel-image');
            const preview = document.getElementById('channel-image-preview');
            const filename = document.getElementById('channel-image-filename');
            if (!input || !preview || !filename) return;

            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (!file) {
                    filename.textContent = 'Henüz dosya seçilmedi';
                    preview.innerHTML = '';
                    preview.innerHTML = '<span aria-hidden="true">＋</span>';
                    return;
                }

                filename.textContent = file.name;
                if (!file.type.startsWith('image/')) return;

                const reader = new FileReader();
                reader.onload = event => {
                    preview.innerHTML = '';
                    const image = document.createElement('img');
                    image.src = event.target.result;
                    image.alt = 'Seçilen kategori görselinin önizlemesi';
                    preview.appendChild(image);
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>
@endsection
