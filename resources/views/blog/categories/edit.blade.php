@extends('layouts.repost')

@section('title', $category->name . ' — Topluluk ayarları')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">TOPLULUK AYARLARI</span><h1>{{ $category->name }} düzenle</h1></div></div>
    <form method="POST" action="{{ route('blog.category.update', $category->slug) }}" enctype="multipart/form-data" class="card card-body repost-settings-form">
        @csrf
        @method('PUT')
        <label for="name">Topluluk adı</label><input id="name" name="name" value="{{ old('name', $category->name) }}" maxlength="255" required>
        @error('name')<span class="repost-form-error">{{ $message }}</span>@enderror
        <label for="description">Açıklama</label><textarea id="description" name="description" rows="5" maxlength="5000">{{ old('description', $category->description) }}</textarea>
        <label for="profile_image">Yeni profil görseli</label><input id="profile_image" type="file" name="profile_image" accept="image/*">
        <label for="cover_image">Yeni kapak görseli</label><input id="cover_image" type="file" name="cover_image" accept="image/*">
        <button class="btn btn-primary btn-rounded" type="submit">Değişiklikleri kaydet</button>
    </form>
    <form method="POST" action="{{ route('blog.category.destroy', $category->slug) }}" class="card card-body repost-danger-zone" onsubmit="return confirm('Bu topluluğu silmek istediğine emin misin?')">
        @csrf
        @method('DELETE')
        <strong>Topluluğu sil</strong>
        <p class="meta">Bu işlem geri alınamaz.</p>
        <button class="btn btn-secondary btn-rounded" type="submit">Topluluğu sil</button>
    </form>
@endsection
