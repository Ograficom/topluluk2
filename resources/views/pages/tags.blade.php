@extends('layouts.app')

@section('title', 'Etiket bulutu')

@section('content')
    <article class="card card-body">
        <header class="card-header"><h1 class="h3">Etiket bulutu</h1><p class="small color-gray">Yayımlanmış yazılardaki konulara göre keşfet.</p></header>
        <div class="widget__content tagscloud">
            @forelse ($tags as $tag => $count)
                <span><a href="{{ route('tags.show', ['tag' => $tag]) }}">{{ $tag }} <small class="color-gray">{{ $count }}</small></a></span>
            @empty
                <p class="color-gray">Henüz etiket eklenmemiş.</p>
            @endforelse
        </div>
    </article>
@endsection
