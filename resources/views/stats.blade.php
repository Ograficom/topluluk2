@extends('layouts.app')

@section('title', 'OgraFi istatistikleri')

@section('content')
    <header class="repost-page-heading">
        <div><span class="repost-eyebrow">OGRAFI TOPLULUĞU</span><h1>İstatistikler</h1><p>Topluluk etkinliğine genel bakış.</p></div>
    </header>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <section class="card card-body"><span class="small color-gray">Üye sayısı</span><strong class="h3">{{ number_format((int)($stats['users'] ?? 0)) }}</strong></section>
        <section class="card card-body"><span class="small color-gray">Gönderi sayısı</span><strong class="h3">{{ number_format((int)($stats['posts'] ?? 0)) }}</strong></section>
        <section class="card card-body"><span class="small color-gray">Yorum sayısı</span><strong class="h3">{{ number_format((int)($stats['comments'] ?? 0)) }}</strong></section>
        <section class="card card-body"><span class="small color-gray">Topluluk sayısı</span><strong class="h3">{{ number_format((int)($stats['categories'] ?? 0)) }}</strong></section>
    </div>
    <section class="card card-body"><p class="color-gray">Ayrıntılı site sağlığı ölçümleri yönetim panelinde bulunur.</p><a class="btn btn-secondary btn-rounded" href="{{ url('/dashboard/site-health') }}">Site sağlığını görüntüle</a></section>
@endsection
