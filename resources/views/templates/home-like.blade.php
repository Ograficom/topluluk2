@extends('layouts.repost')

@section('title', 'OgraFi arayüz önizlemesi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">ÖNİZLEME</span><h1>OgraFi topluluk arayüzü</h1><p>Bu sayfa, canlı akış tasarımının önizleme adresidir.</p></div><a href="{{ route('home') }}" class="btn btn-primary btn-rounded">Ana sayfayı aç</a></div>
@endsection
