@extends('layouts.app')

@section('title', $page['title'])

@section('content')
    <article class="card card-body">
        <h1>{{ $page['title'] }}</h1>

        <div class="editor">
            @foreach ($page['paragraphs'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>

        <div class="static__footer">
            <a class="btn btn-sm btn-secondary" href="{{ route('contacts') }}">Bizimle iletişime geç</a>
        </div>
    </article>
@endsection
