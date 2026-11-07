@extends('layouts.app')

@section('title', 'İletişim')

@section('content')
    <section class="feed">
        <form action="{{ route('contacts.send') }}" method="POST" enctype="multipart/form-data" class="card card-body">
            @csrf
            <h1 class="h3">Destek ekibiyle iletişim</h1>
            <ul class="feedback__list">
                @auth
                    <li>
                        <div style="display:flex;align-items:center;gap:13px;padding:13px 15px;border:1px solid var(--border-color);border-radius:14px;background:var(--body-bg);">
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="" width="46" height="46" style="width:46px;height:46px;flex:0 0 46px;object-fit:cover;border-radius:50%;">
                            <div style="min-width:0;display:flex;flex-direction:column;gap:2px;">
                                <strong style="font-size:.95rem;line-height:1.35;color:var(--title-color);">{{ auth()->user()->displayName() }}</strong>
                                <span style="font-size:.8rem;color:var(--color-gray);">Oturum açmış hesap</span>
                            </div>
                            <span aria-hidden="true" style="margin-left:auto;color:var(--color-primary);font-size:.8rem;font-weight:600;">Hesabın</span>
                        </div>
                    </li>
                @else
                    <li class="form-group">
                        <label class="visually-hidden" for="contact-name">Adın</label>
                        <input id="contact-name" name="name" type="text" value="{{ old('name') }}" maxlength="35" autocomplete="name" placeholder="Adın" required>
                        @error('name')<span class="small color-red">{{ $message }}</span>@enderror
                    </li>
                    <li class="form-group">
                        <label class="visually-hidden" for="contact-email">E-posta adresin</label>
                        <input id="contact-email" name="email" type="email" value="{{ old('email') }}" maxlength="254" autocomplete="email" placeholder="E-posta adresin" required>
                        @error('email')<span class="small color-red">{{ $message }}</span>@enderror
                    </li>
                @endauth
                <li class="form-group">
                    <label class="visually-hidden" for="contact-sub">Konu</label>
                    <input id="contact-sub" name="subject" type="text" value="{{ old('subject') }}" maxlength="45" placeholder="Konu" required>
                    @error('subject')<span class="small color-red">{{ $message }}</span>@enderror
                </li>
                <li class="form-group">
                    <label class="visually-hidden" for="contact-recipient">İlgili birim</label>
                    <select id="contact-recipient" name="recipient" required>
                        <option value="" disabled @selected(old('recipient') === null)>Bir birim seç</option>
                        <option value="support" @selected(old('recipient') === 'support')>Teknik destek</option>
                        <option value="editorial" @selected(old('recipient') === 'editorial')>Editör ekibi</option>
                        <option value="partnerships" @selected(old('recipient') === 'partnerships')>İş birlikleri</option>
                        <option value="bug" @selected(old('recipient') === 'bug')>Hata bildirimi</option>
                        <option value="suggestion" @selected(old('recipient') === 'suggestion')>Görüş ve öneri</option>
                    </select>
                    @error('recipient')<span class="small color-red">{{ $message }}</span>@enderror
                </li>
                <li class="form-group">
                    <label class="visually-hidden" for="contact-message">Mesajın</label>
                    <textarea id="contact-message" name="message" rows="8" maxlength="10000" placeholder="Mesajını yaz..." required>{{ old('message') }}</textarea>
                    @error('message')<span class="small color-red">{{ $message }}</span>@enderror
                </li>
                <li class="form-group">
                    <label class="small color-gray" for="contact-attachments">Dosya ekleri (en fazla 5 dosya, her biri 2 MB)</label>
                    <input id="contact-attachments" class="form-attachments" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,.txt,.doc,.docx">
                    @error('attachments')<span class="small color-red">{{ $message }}</span>@enderror
                    @error('attachments.*')<span class="small color-red">{{ $message }}</span>@enderror
                </li>
                <li>
                    <button class="btn btn-primary" name="send_btn" type="submit">Mesajı gönder</button>
                </li>
            </ul>
            @if (blank($supportEmail))
                <p class="small color-gray">Destek e-posta adresi yapılandırılmamış. Şu anda mesaj gönderilemez.</p>
            @endif
        </form>
    </section>
@endsection
