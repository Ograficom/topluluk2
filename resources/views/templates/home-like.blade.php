@extends('layouts.repost')

@section('title', 'OgraFi arayüz önizlemesi')
@section('content')
    <div data-ografi-layout-container="home">
        <section data-ografi-layout-section="feed_filters" class="repost-feed-heading">
            <div><span class="repost-eyebrow">ÖNİZLEME</span><h1>OgraFi topluluk akışı</h1><p>Akış filtreleri ve içerik görünümü.</p></div>
            <a href="{{ route('home') }}" class="btn btn-primary btn-rounded">Ana sayfayı aç</a>
        </section>
        <section data-ografi-layout-section="read_filters" class="card card-body">
            <strong>Okuma filtreleri</strong><p class="color-gray">Son gönderiler, takip edilenler ve keşfet.</p>
        </section>
        <section data-ografi-layout-section="post_feed" class="card card-body">
            <strong>Gönderi akışı</strong><p class="color-gray">Canlı içerikler ana sayfada listelenir.</p>
        </section>
    </div>
@endsection
