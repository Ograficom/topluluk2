@extends('layouts.repost')

@section('title', 'Sayfa bulunamadı — OgraFi')
@section('content')
    <section class="card card-body repost-empty-state repost-error-state">
        <span class="repost-eyebrow">404</span>
        <h1>Aradığın sayfa burada değil.</h1>
        <p>Bağlantı değişmiş veya içerik kaldırılmış olabilir.</p>
        <a href="{{ route('home') }}" class="btn btn-primary btn-rounded">Ana sayfaya dön</a>
    </section>
@endsection
