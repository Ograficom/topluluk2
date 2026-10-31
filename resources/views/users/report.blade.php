@extends('layouts.repost')

@section('title', 'Profili bildir — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">GÜVENLİK</span><h1>Profili bildir</h1><p>{{ $user->name }} kullanıcısını bildir.</p></div></div>
    <form method="POST" action="{{ route('users.report', $user->username) }}" class="card card-body repost-settings-form">
        @csrf
        <label for="topic">Bildirim nedeni</label><select id="topic" name="topic" required>@foreach($topics as $topic)<option value="{{ $topic }}" @selected(old('topic') === $topic)>{{ $topic }}</option>@endforeach</select>
        <label for="message">Açıklama (isteğe bağlı)</label><textarea id="message" name="message" rows="5" maxlength="2000">{{ old('message') }}</textarea>
        <label class="repost-settings-check"><input type="checkbox" name="show_username" value="1" @checked(old('show_username'))> Bildirimde kullanıcı adım gösterilsin</label>
        <label class="repost-settings-check"><input type="checkbox" name="terms" value="1" required> Bildirimin doğru olduğunu onaylıyorum.</label>
        <button class="btn btn-primary btn-rounded" type="submit">Bildirimi gönder</button>
    </form>
@endsection
