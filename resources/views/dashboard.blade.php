@extends('layouts.repost')

@section('title', 'Hesap merkezi — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">HESAP MERKEZİ</span><h1>Hesap ayarları</h1><p>Profilini, güvenliğini ve topluluk tercihlerini yönet.</p></div></div>
    <div class="repost-dashboard-grid">
        <a class="card card-body repost-dashboard-tile" href="{{ route('profile.edit') }}"><strong>Profil bilgileri</strong><span>Hakkında, konum ve sosyal bağlantılar</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('dashboard.account') }}"><strong>Hesap bilgileri</strong><span>Ad, kullanıcı adı ve e-posta</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('dashboard.password') }}"><strong>Şifre</strong><span>Hesap şifreni değiştir</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('dashboard.privacy') }}"><strong>Gizlilik</strong><span>Profil ve içerik görünürlüğü</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('dashboard.preferences') }}"><strong>Tercihler</strong><span>Dil ve içerik tercihleri</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('dashboard.notifications') }}"><strong>Bildirimler</strong><span>Bildirim ve e-posta tercihleri</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('dashboard.blocks') }}"><strong>Engellenenler</strong><span>Engellediğin üyeleri yönet</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('dashboard.badges') }}"><strong>Rozetler</strong><span>Kazandığın rozetler ve puanlar</span></a>
        <a class="card card-body repost-dashboard-tile" href="{{ route('messages.settings') }}"><strong>Mesaj ayarları</strong><span>Kimlerin mesaj gönderebileceği</span></a>
    </div>
@endsection
