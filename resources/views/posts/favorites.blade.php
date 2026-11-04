@extends('layouts.app')

@section('title', 'Favorilerim')

@section('content')
    <section class="feed">
        <section class="hero">
            <h1 class="h2 hero__title">Favorilerim</h1>
            <div class="speedbar">
                <a href="{{ route('posts.index') }}">OgraFi</a>
                <span>/</span>
                <span>Kaydedilen yazılar</span>
            </div>
        </section>
        @forelse ($posts as $post)
            @include('posts._card', ['post' => $post])
        @empty
            <section class="card card-body empty-state">
                <h2 class="h4">Henüz kayıtlı yazı yok</h2>
                <p class="color-gray">Yazıların altındaki yer imi simgesini kullanarak favorilerine ekleyebilirsin.</p>
                <a href="{{ route('posts.index') }}" class="btn btn-primary btn-md">Yazıları keşfet</a>
            </section>
        @endforelse
        @include('layouts._pagination', ['paginator' => $posts])
    </section>
@endsection
