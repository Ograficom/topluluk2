@extends('layouts.app')

@section('title', '#'.$tag)

@section('content')
    <header class="hero__header-main"><div><h1 class="h3">#{{ $tag }}</h1><p class="small color-gray">Bu etikete sahip yazılar.</p></div><a href="{{ route('tags.index') }}" class="link">Tüm etiketler</a></header>
    @forelse ($posts as $post)
        @include('posts._card', ['post' => $post])
    @empty
        <section class="card card-body empty-state"><h2 class="h4">Bu etikette henüz yazı yok</h2></section>
    @endforelse
    {{ $posts->links() }}
@endsection
