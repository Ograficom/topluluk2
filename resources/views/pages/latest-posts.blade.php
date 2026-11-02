@extends('layouts.app')

@section('title', 'Son yazılar')

@section('content')
    <header class="hero__header-main"><div><h1 class="h3">Son yazılar</h1><p class="small color-gray">OgraFi topluluğunda yayımlanan en yeni içerikler.</p></div></header>
    @forelse ($posts as $post)
        @include('posts._card', ['post' => $post])
    @empty
        <section class="card card-body empty-state"><h2 class="h4">Henüz yayın yok</h2></section>
    @endforelse
    {{ $posts->links() }}
@endsection
