@extends('layouts.repost')

@section('title', 'Ayarlar — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">AYARLAR</span><h1>Hesap ve site ayarları</h1><p>Hesap tercihlerini buradan yönetebilirsin.</p></div></div>
    <div class="repost-dashboard-grid">
        <a class="card card-body repost-dashboard-tile" href="{{ route('dashboard') }}"><strong>Hesap merkezi</strong><span>Profil, gizlilik ve güvenlik</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('messages.settings') }}"><strong>Mesaj tercihleri</strong><span>Mesaj alımını yönet</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('dashboard.notifications') }}"><strong>Bildirimler</strong><span>Bildirim ayarları</span></a>
    </div>
@endsection
