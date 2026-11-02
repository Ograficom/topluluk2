@extends('layouts.app')

@section('title', $page['title'])

@section('content')
    <article class="card card-body">
        <header class="card-header"><h1 class="h3">{{ $page['title'] }}</h1></header>
        <div class="editor">
            @foreach ($page['paragraphs'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach
        </div>
        <footer class="card-footer"><a class="link" href="{{ route('contacts') }}">Bizimle iletişime geç</a></footer>
    </article>
@endsection
