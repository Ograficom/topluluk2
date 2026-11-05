@extends('layouts.app')

@section('title', 'Etiket bulutu')

@section('content')
    <section class="feed">
        <article class="card card-body">
            <header class="card-header">
                <h1 class="h3 card-title">Etiket bulutu</h1>
                <p class="small color-gray">Yayımlanmış yazılardaki konulara göre keşfet.</p>
            </header>
            <div class="widget__content tagscloud">
                @forelse ($tags as $tag)
                    <span><a href="{{ route('tags.show', ['tag' => $tag->slug]) }}">{{ $tag->name }} <small class="color-gray">{{ $tag->posts_count }}</small></a></span>
                @empty
                    <p class="color-gray">Henüz etiket eklenmemiş.</p>
                @endforelse
            </div>
        </article>
    </section>
@endsection
