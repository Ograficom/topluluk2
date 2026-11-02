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

        <form class="setting__box setting__list" action="{{ route('channels.store') }}" method="POST">
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
@endsection
