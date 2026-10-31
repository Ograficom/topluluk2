@extends('layouts.repost')

@section('title', 'Sıkça Sorulan Sorular — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">YARDIM MERKEZİ</span><h1>Sıkça Sorulan Sorular</h1><p>OgraFi topluluğunu kullanırken merak ettiklerin.</p></div></div>
    <div class="repost-faq-list">
        <details class="card card-body"><summary>OgraFi nedir?</summary><p>OgraFi, kullanıcıların yazı, fikir ve deneyimlerini paylaşabildiği topluluk platformudur.</p></details>
        <details class="card card-body"><summary>Nasıl gönderi paylaşabilirim?</summary><p>Hesabına giriş yaptıktan sonra üst menüdeki “Yazı yaz” bağlantısını kullanabilirsin.</p></details>
        <details class="card card-body"><summary>Bir topluluğa nasıl katılırım?</summary><p>Topluluklar sayfasından ilgilendiğin alanı açıp katılma seçeneğini kullanabilirsin.</p></details>
        <details class="card card-body"><summary>Hesabımı nasıl yönetebilirim?</summary><p>Hesap ayarları bölümünden profil bilgilerini, gizlilik ve bildirim tercihlerini düzenleyebilirsin.</p></details>
        <details class="card card-body"><summary>Bir içeriği nasıl bildirebilirim?</summary><p>Gönderi veya profil üzerindeki bildirme seçeneğini kullanabilirsin.</p></details>
    </div>
@endsection
