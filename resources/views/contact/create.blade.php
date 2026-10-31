@extends('layouts.repost')

@section('title', $pageTitle . ' — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">DESTEK</span><h1>Bize ulaşın</h1><p>Soru, öneri veya geri bildirimin için mesaj gönder.</p></div></div>
    @if(session('contact_status'))<div class="card card-body repost-auth-status">{{ session('contact_status') }}</div>@endif
    <form method="POST" action="{{ route('contact.store') }}" class="card card-body repost-settings-form">
        @csrf
        <label for="full_name">Ad soyad</label><input id="full_name" name="full_name" value="{{ old('full_name', $user?->name) }}" required>
        <label for="email">E-posta</label><input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" required>
        <label for="subject">Konu</label><input id="subject" name="subject" value="{{ old('subject') }}" required>
        <label for="message">Mesaj</label><textarea id="message" name="message" rows="6" maxlength="5000" required>{{ old('message') }}</textarea>
        <label class="repost-settings-check"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))> Mesajımın yanıtlanması için bilgilerimin işlenmesini kabul ediyorum.</label>
        <button class="btn btn-primary btn-rounded" type="submit">Mesajı gönder</button>
    </form>
@endsection
