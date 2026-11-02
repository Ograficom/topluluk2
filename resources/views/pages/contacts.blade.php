@extends('layouts.app')

@section('title', 'İletişim')

@section('content')
    <article class="card card-body">
        <header class="card-header"><h1 class="h3">İletişim</h1><p class="small color-gray">Öneri, hata bildirimi ve destek taleplerin için bize ulaş.</p></header>
        <div class="editor">
            <p>Hesap, içerik veya teknik sorunlarla ilgili mesajını e-posta üzerinden iletebilirsin.</p>
            @if (filled($supportEmail))
                <p><a class="btn btn-primary btn-md" href="mailto:{{ $supportEmail }}?subject={{ rawurlencode('OgraFi destek talebi') }}">Destek e-postası gönder</a></p>
                <p class="small color-gray">{{ $supportEmail }}</p>
            @else
                <p class="color-gray">Destek e-posta adresi henüz yapılandırılmamış. Site yönetimiyle iletişime geç.</p>
            @endif
        </div>
    </article>
@endsection
