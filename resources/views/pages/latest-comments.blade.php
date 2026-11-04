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

        @if (auth()->user()?->is_admin)
            <form action="{{ route('comments.moderate') }}" method="POST" id="latest-comments-moderation-form">
                @csrf
                <div class="comments-tree-list">
                    @forelse ($comments as $comment)
                        @include('posts._comment', ['comment' => $comment, 'post' => $comment->post])
                    @empty
                        <section class="card card-body empty-state"><h2 class="h4">Henüz yorum yok</h2></section>
                    @endforelse
                </div>
                @if ($comments->isNotEmpty())
                    <div class="mass_comments_action">
                        <label for="latest-comments-action">Seçili yorumlar:</label>
                        <select class="form-select" id="latest-comments-action" name="action" required>
                            <option value="">— İşlem —</option>
                            <option value="delete">Sil</option>
                        </select>
                        <button class="btn btn-primary btn-md" type="submit">Uygula</button>
                    </div>
                @endif
            </form>
        @else
            <div class="comments-tree-list">
                @forelse ($comments as $comment)
                    @include('posts._comment', ['comment' => $comment, 'post' => $comment->post])
                @empty
                    <section class="card card-body empty-state"><h2 class="h4">Henüz yorum yok</h2></section>
                @endforelse
            </div>
        @endif

        @include('layouts._pagination', ['paginator' => $comments])
    </section>
@endsection
