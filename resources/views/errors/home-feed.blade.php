@extends('layouts.app')

@section('title', 'Ografi')

@section('content')
    <div class="alma-home-feed-error" role="alert">
        <div class="alma-home-feed-error__inner">
            <div class="alma-home-feed-error__icon" aria-hidden="true">
                <iconify-icon icon="lucide:shield-alert"></iconify-icon>
            </div>

            <p class="alma-home-feed-error__title">Sunucu geçersiz sayfa verisi döndürdü.</p>

            <div class="alma-home-feed-error__actions">
                <button
                    type="button"
                    class="alma-home-feed-error__button"
                    onclick="window.location.reload()"
                >
                    <iconify-icon icon="lucide:refresh-cw"></iconify-icon>
                    <span>Tekrarlamak</span>
                </button>

                <a href="{{ route('home') }}" class="alma-home-feed-error__button">
                    <iconify-icon icon="lucide:house"></iconify-icon>
                    <span>Ev</span>
                </a>
            </div>
        </div>
    </div>
@endsection
