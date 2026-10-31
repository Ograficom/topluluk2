@extends('layouts.repost')

@section('title', 'Topluluk oluştur — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">YENİ TOPLULUK</span><h1>Topluluk oluştur</h1><p>İlgi alanını bir araya getirecek yeni bir alan aç.</p></div></div>
    <form method="POST" action="{{ route('blog.category.store') }}" enctype="multipart/form-data" class="card card-body repost-settings-form">
        @csrf
        <label for="name">Topluluk adı</label><input id="name" name="name" value="{{ old('name') }}" maxlength="255" required>
        @error('name')<span class="repost-form-error">{{ $message }}</span>@enderror
        <label for="slug">Adres (isteğe bağlı)</label><input id="slug" name="slug" value="{{ old('slug') }}" maxlength="255" placeholder="otomatik oluşturulur">
        @error('slug')<span class="repost-form-error">{{ $message }}</span>@enderror
        <label for="description">Açıklama</label><textarea id="description" name="description" rows="5" maxlength="5000">{{ old('description') }}</textarea>
        <label for="profile_image">Profil görseli</label><input id="profile_image" type="file" name="profile_image" accept="image/*">
        <label for="cover_image">Kapak görseli</label><input id="cover_image" type="file" name="cover_image" accept="image/*">
        <button class="btn btn-primary btn-rounded" type="submit">Topluluğu oluştur</button>
    </form>
@endsection
