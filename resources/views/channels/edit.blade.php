@extends('layouts.app')

@section('title', 'Kategori düzenle')

@section('content')
    <section class="subscriptions__section card card-body">
        <a href="{{ route('channels.show', $channel) }}" class="link-accent">← Kategoriye dön</a>
        <h1 class="h3">Kategori düzenle</h1>

        @if ($errors->any())
            <div class="allert" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="setting__box setting__list" action="{{ route('channels.update', $channel) }}" method="POST" enctype="multipart/form-data">
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
                </span>
            </label>
            <label class="setting__list-item" for="channel-image">
                <span class="setting__list-label">Kategori profil resmi</span>
                <span class="setting__list-value">
                    @if ($channel->image_path)
                        <img src="{{ asset('storage/'.$channel->image_path) }}" alt="{{ $channel->name }}" width="80" height="80">
                        <label><input type="checkbox" name="remove_image" value="1"> Mevcut resmi kaldır</label>
                    @endif
                    <input id="channel-image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                    <span class="small color-gray">Yeni görsel seçersen mevcut görsel değiştirilir. JPG, PNG veya WebP · En fazla 5 MB</span>
                </span>
            </label>
            <label class="setting__list-item" for="channel-description">
                <span class="setting__list-label">Açıklama</span>
                <span class="setting__list-value">
                    <textarea id="channel-description" name="description" rows="4" maxlength="2000">{{ old('description', $channel->description) }}</textarea>
                </span>
            </label>
            <div class="subscriptions__action">
                <button class="btn btn-primary" type="submit">Değişiklikleri kaydet</button>
            </div>
        </form>
    </section>
@endsection
