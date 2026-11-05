@extends('layouts.app')

@section('title', '#'.$tag)
@section('description', 'OgraFi üzerinde #'.$tag.' etiketiyle yayınlanan yazılar.')

@section('content')
    <section class="feed">
        <section class="hero">
            <h1 class="h2 hero__title">#{{ $tag }}</h1>
            <div class="speedbar">
                <a href="{{ route('posts.index') }}">OgraFi</a>
                <span>/</span>
                <a href="{{ route('tags.index') }}">Etiket bulutu</a>
                <span>/</span>
                <span>{{ $tag }}</span>
            </div>
        </section>
        @forelse ($posts as $post)
            @include('posts._card', ['post' => $post])
        @empty
            <section class="card card-body empty-state"><h2 class="h4">Bu etikette henüz yazı yok</h2></section>
        @endforelse
        @include('layouts._pagination', ['paginator' => $posts])
    </section>
@endsection
