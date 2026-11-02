@extends('layouts.app')

@section('title', 'Favorilerim')

@section('content')
    <header class="hero__header-main"><div><h1 class="h3">Favorilerim</h1><p class="small color-gray">Daha sonra okumak için kaydettiğin yazılar.</p></div></header>
    @forelse ($posts as $post)
        @include('posts._card', ['post' => $post])
    @empty
        <section class="card card-body empty-state">
            <h2 class="h4">Henüz kayıtlı yazı yok</h2>
            <p class="color-gray">Yazıların altındaki yer imi simgesini kullanarak favorilerine ekleyebilirsin.</p>
            <a href="{{ route('posts.index') }}" class="btn btn-primary btn-md">Yazıları keşfet</a>
        </section>
    @endforelse
    {{ $posts->links() }}
@endsection
