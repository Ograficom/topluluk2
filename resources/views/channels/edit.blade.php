@extends('layouts.app')

@section('title', 'Kategori düzenle')

@section('content')
    <section class="subscriptions__section card card-body channel-edit">
        <a href="{{ route('channels.show', $channel) }}" class="link-accent channel-edit__back">
            <x-repost-icon name="arrow-left" size="16" />
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
                    <span class="channel-edit__hint">Kanalın bağlantı adresi.</span>
                </span>
            </label>

            <div class="setting__list-item channel-edit__image-row">
                <span class="setting__list-label">Kanal profil resmi</span>
                <div class="setting__list-value">
                    <div class="channel-image-upload">
                        <div class="channel-image-upload__preview-wrap">
                            <div class="channel-image-upload__preview" id="channel-image-preview">
                                @if ($channel->image_path)
                                    <img src="{{ asset('storage/'.$channel->image_path) }}" alt="{{ $channel->name }}" id="channel-image-preview-img">
                                @else
                                    <x-repost-icon name="image" size="34" />
                                @endif
                            </div>
                            <span class="channel-image-upload__status">Kanal görseli</span>
                        </div>

                        <div class="channel-image-upload__content">
                            <strong>Profil görselini güncelle</strong>
                            <p>Markanı veya kanalını en iyi yansıtan görseli seç.</p>

                            <div class="channel-image-upload__actions">
                                <label class="channel-image-upload__button" for="channel-image">
                                    <x-repost-icon name="upload" size="17" />
                                    <span>Görsel seç</span>
                                </label>
                                <span class="channel-image-upload__filename" id="channel-image-filename">Henüz dosya seçilmedi</span>
                            </div>

                            <span class="channel-image-upload__hint">JPG, PNG veya WebP · En fazla 5 MB</span>

                            @if ($channel->image_path)
                                <label class="channel-image-upload__remove">
                                    <input type="checkbox" name="remove_image" value="1" id="channel-remove-image">
                                    <span>Mevcut profil görselini kaldır</span>
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
                    <span class="channel-edit__hint">Kanalın ne hakkında olduğunu kısaca anlat.</span>
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
