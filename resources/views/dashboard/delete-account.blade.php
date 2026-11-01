@extends('layouts.repost')

@section('title', 'Hesabı sil — OgraFi')
@section('content')
    <div class="repost-feed-heading"><div><span class="repost-eyebrow">HESAP GÜVENLİĞİ</span><h1>Hesabı sil</h1><p>Bu işlem hesabına ve ilişkili verilere erişimini sonlandırır.</p></div></div>
    <section class="card card-body repost-danger-zone">
        <strong>Hesabını kalıcı olarak silmek üzeresin.</strong>
        <p>Devam etmeden önce kararını dikkatlice değerlendir. Silme işlemi geri alınamaz.</p>
        <form method="POST" action="{{ route('dashboard.delete-account.destroy') }}" class="repost-settings-form" onsubmit="return confirm('Hesabını kalıcı olarak silmek istediğine emin misin?')">
            @csrf
            @method('DELETE')
            <label for="password">Onay için şifren</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')<span class="repost-form-error">{{ $message }}</span>@enderror
            <button class="btn btn-secondary btn-rounded" type="submit">Hesabımı kalıcı olarak sil</button>
        </form>
    </section>
@endsection
