@extends('layouts.app')

@section('title', 'Takip akışı')

@section('content')
    <header class="hero__header-main"><div><h1 class="h3">Takip akışı</h1><p class="small color-gray">Takip ettiğin üyelerin yeni yazıları.</p></div></header>
    @forelse ($posts as $post)
        @include('posts._card', ['post' => $post])
    @empty
        <section class="card card-body empty-state">
            <h2 class="h4">Akışın henüz boş</h2>
            <p class="color-gray">İlgi duyduğun yazarları takip ettiğinde yazıları burada görünecek.</p>
            <a href="{{ route('members.index') }}" class="btn btn-primary btn-md">Yazarları keşfet</a>
        </section>
    @endforelse
    {{ $posts->links() }}
@endsection
