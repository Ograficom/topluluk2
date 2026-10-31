@extends('layouts.repost')

@section('title', 'Gönderiyi bildir — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">GÜVENLİK</span><h1>Gönderiyi bildir</h1><p>{{ $post->title }}</p></div></div>
    <form method="POST" action="{{ route('blog.post.report', $post->slug) }}" class="card card-body repost-settings-form">
        @csrf
        <label for="topic">Bildirim nedeni</label><select id="topic" name="topic" required>@foreach($topics as $key => $label)<option value="{{ $key }}" @selected(old('topic') === $key)>{{ $label }}</option>@endforeach</select>
        <label for="message">Açıklama (isteğe bağlı)</label><textarea id="message" name="message" rows="5" maxlength="2000">{{ old('message') }}</textarea>
        <button class="btn btn-primary btn-rounded" type="submit">Bildirimi gönder</button>
    </form>
@endsection
