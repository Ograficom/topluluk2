@extends('layouts.app')

@section('title', 'Yorumla yeniden paylaş')
@section('robots', 'noindex,follow')

@section('content')
    <section class="card card-body post-quote-composer">
        <header class="post-quote-composer__header">
            <a href="{{ route('posts.show', $source) }}" class="btn btn-secondary-ghost btn-circle btn-md" aria-label="Geri dön"><x-repost-icon name="chevron-left" size="20" /></a>
            <h1 class="h3">Yorumla yeniden paylaş</h1>
        </header>
        <form method="POST" action="{{ route('posts.reposts.toggle', $source) }}">
            @csrf
            <label for="quote-comment">Yorumunuz</label>
            <textarea id="quote-comment" name="quote_comment" rows="5" maxlength="5000" required placeholder="Bu gönderi hakkında ne düşünüyorsunuz?" autofocus>{{ old('quote_comment') }}</textarea>
            @error('quote_comment')<p class="color-red" role="alert">{{ $message }}</p>@enderror
            @include('posts._quoted-post', ['source' => $source])
            <div class="post-quote-composer__actions">
                <a class="btn btn-secondary" href="{{ route('posts.show', $source) }}">Vazgeç</a>
                <button class="btn btn-primary" type="submit">Yeniden paylaş</button>
            </div>
        </form>
    </section>
@endsection
