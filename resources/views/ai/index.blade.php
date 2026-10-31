@extends('layouts.repost')

@section('title', 'Yapay zeka — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">ARAÇLAR</span><h1>Yapay zeka asistanı</h1><p>OgraFi yapay zeka özelliği şu anda devre dışı.</p></div></div>
    <section class="card card-body repost-empty-state">
        <h2>Şu anda kullanılamıyor</h2>
        <p>Bu özellik yeniden etkinleştirildiğinde burada duyurulacak.</p>
        <a href="{{ route('home') }}" class="btn btn-secondary btn-rounded">Ana akışa dön</a>
    </section>
@endsection
