@extends('layouts.app')

@section('title', 'Bildirimler')
@section('robots', 'noindex,follow')

@push('head')
    <style>
        .notification-inbox .notification-item:has(details[open]){flex-wrap:wrap}
        .notification-inbox .notification-item:has(details[open]) .notification-item__actions{flex:0 0 100%;display:flex;flex-direction:column;align-items:flex-end}
        .notification-inbox .notification-item__actions-menu{position:static!important;width:100%;box-shadow:none}
        .notification-inbox__toolbar{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0}
        .notification-inbox__toolbar form{margin:0}
    </style>
@endpush

@section('content')
    <section class="card card-body notification-inbox">
        <h1 class="h3">Bildirimler</h1>
        <div class="notification-inbox__toolbar">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">Tümünü okundu işaretle</button>
            </form>
            <form method="POST" action="{{ route('notifications.destroy-all') }}" onsubmit="return confirm('Tüm bildirimlerini silmek istiyor musun?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-red btn-sm">Tümünü sil</button>
            </form>
        </div>
        @include('layouts._notification-items', ['headerNotifications' => $notifications])
        @include('layouts._pagination', ['paginator' => $notifications])
    </section>
@endsection
