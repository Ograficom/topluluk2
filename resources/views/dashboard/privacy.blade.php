@extends('layouts.repost')

@section('title', 'Gizlilik — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">GİZLİLİK</span><h1>Görünürlük ayarları</h1><p>Takipçi, gönderi ve yorum görünürlüğünü yönet.</p></div></div>
    <form method="POST" action="{{ route('dashboard.privacy.update') }}" class="card card-body repost-settings-form">
        @csrf
        @method('PUT')
        @foreach(['following_visibility' => 'Takip ettiklerim', 'posts_visibility' => 'Gönderilerim', 'comments_visibility' => 'Yorumlarım'] as $field => $label)
            <label for="{{ $field }}">{{ $label }}</label>
            <select id="{{ $field }}" name="{{ $field }}" required>
                <option value="public" @selected(old($field, auth()->user()->{$field}) === 'public')>Herkese açık</option>
                <option value="friends" @selected(old($field, auth()->user()->{$field}) === 'friends')>Karşılıklı takipçiler</option>
                <option value="private" @selected(old($field, auth()->user()->{$field}) === 'private')>Yalnızca ben</option>
            </select>
            @error($field)<span class="repost-form-error">{{ $message }}</span>@enderror
        @endforeach
        <button class="btn btn-primary btn-rounded" type="submit">Gizlilik ayarlarını kaydet</button>
    </form>
@endsection
