@extends('layouts.app')

@section('title', 'Son yorumlar')

@section('content')
    <section class="feed">
        <section class="hero">
            <h1 class="h2 hero__title">Son yorumlar</h1>
            <div class="speedbar">
                <a href="{{ route('posts.index') }}">OgraFi</a>
                <span>/</span>
                <span>Yorumlar</span>
            </div>
        </section>

        @if (auth()->user()?->is_admin && $comments->isNotEmpty())
            <form action="{{ route('comments.moderate') }}" method="POST" id="latest-comments-moderation-form" class="mass_comments_action">
                @csrf
                <label for="latest-comments-action">Seçili yorumlar:</label>
                <select class="form-select" id="latest-comments-action" name="action" required>
                    <option value="">— İşlem —</option>
                    <option value="delete">Sil</option>
                </select>
                <button class="btn btn-primary btn-md" type="submit">Uygula</button>
            </form>
        @endif

        <div class="comments-tree-list">
            @forelse ($comments as $comment)
                @include('posts._comment', ['comment' => $comment, 'post' => $comment->post, 'moderationFormId' => 'latest-comments-moderation-form'])
            @empty
                <section class="card card-body empty-state"><h2 class="h4">Henüz yorum yok</h2></section>
            @endforelse
        </div>

        @include('layouts._pagination', ['paginator' => $comments])
    </section>
@endsection
