@extends('layouts.app')

@section('title', 'Son yazılar')

@section('content')
    <section class="feed">
        <section class="hero">
            <h1 class="h2 hero__title">Son yazılar</h1>
            <div class="speedbar">
                <a href="{{ route('posts.index') }}">OgraFi</a>
                <span>/</span>
                <span>Son yayınlar</span>
            </div>
        </section>
        @forelse ($posts as $post)
            @include('posts._card', ['post' => $post])
        @empty
            <section class="card card-body empty-state"><h2 class="h4">Henüz yayın yok</h2></section>
        @endforelse
        @include('layouts._pagination', ['paginator' => $posts])
    </section>
@endsection
