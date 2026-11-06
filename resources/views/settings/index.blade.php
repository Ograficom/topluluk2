@extends('layouts.app')

@section('title', 'Ayarlar')

@section('content')
    <div class="setting main__grid-feed">
        <div data-setting-view>
            <div class="card card-body">
                @if (session('status'))
                    <p class="color-green" role="status">{{ session('status') }}</p>
                @endif
                @if ($errors->any())
                    <div class="color-red" role="alert">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="tabs-content" data-tabs-container="settings">
                    <section
                        class="tabs-panel {{ $activeTab === 'profile' ? 'is-active' : '' }}"
                        id="settings-profile-pane"
                        data-tab-panel="profile"
                        role="tabpanel"
                        aria-labelledby="settings-profile-tab"
                        tabindex="0"
                        @if ($activeTab !== 'profile') hidden @endif
                    >
                        <h1 class="h3">Profil</h1>
                        <form class="setting__box" action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="activeTab" value="profile">
                            <section class="avatar-upload-field">
                                <div class="avatar__upload">
                                    <img class="avatar__upload-image" src="{{ $user->avatarUrl() }}" alt="" width="90" height="90">
                                    <input type="file" id="image" name="avatar" accept="image/jpeg,image/png,image/gif" aria-label="Profil fotoğrafı yükle">
                                </div>
                                <div class="avatar-upload-field__content">
                                    <div class="avatar-upload-field__title">Profil fotoğrafı yükle</div>
                                    <p class="avatar-upload-field__description">JPG, PNG veya GIF. En fazla 2 MB.</p>
                                    <div class="checkbox setting__avatar-upload">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" name="delete_avatar" value="1">
                                            <span>Profil fotoğrafını sil</span>
                                        </label>
                                    </div>
                                </div>
                            </section>

                            <section>
                                <h2 class="h5">Bilgiler</h2>
                                <dl class="setting__list">
                                    <div class="setting__list-item"><dt class="setting__list-label">Üye numarası:</dt><dd class="setting__list-value">{{ $user->id }}</dd></div>
                                    <div class="setting__list-item"><dt class="setting__list-label">Kayıt tarihi:</dt><dd class="setting__list-value">{{ $user->created_at->format('d.m.Y') }}</dd></div>
                                    <div class="setting__list-item"><dt class="setting__list-label">Grup:</dt><dd class="setting__list-value">{{ $user->is_admin ? 'Yöneticiler' : 'Üyeler' }}</dd></div>
                                    <div class="setting__list-item"><dt class="setting__list-label">Kullanıcı adı:</dt><dd class="setting__list-value">{{ $user->username }} <button type="button" class="btn-link link" data-modal-btn="change-username" aria-controls="changeUsernameModal">Değiştir</button></dd></div>
                                </dl>
                            </section>

                            <section class="setting__list">
                                <h2 class="h5">Temel bilgiler</h2>
                                                                <label class="setting__list-item setting__list-item--center">
                                    <span class="setting__list-label">Ad soyad:</span>
                                    <span class="setting__list-value"><input type="text" name="full_name" value="{{ old('full_name', $user->full_name ?: $user->displayName()) }}" maxlength="255" required></span>
                                </label>
                                <label class="setting__list-item">
                                    <span class="setting__list-label">İmza:</span>
                                    <span class="setting__list-value"><textarea name="signature" rows="3" maxlength="500">{{ old('signature', $user->signature) }}</textarea></span>
                                </label>
                                <label class="setting__list-item">
                                    <span class="setting__list-label">Hakkımda:</span>
                                    <span class="setting__list-value"><textarea name="bio" maxlength="2000" rows="5">{{ old('bio', $user->bio) }}</textarea></span>
                                </label>
                            </section>

                            <section class="setting__list">
                                <h2 class="h5">Bağlantılar</h2>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">Web sitesi:</span><span class="setting__list-value"><input type="url" name="website" value="{{ old('website', $user->website) }}" placeholder="https://example.com"></span></label>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">Instagram:</span><span class="setting__list-value"><input type="text" name="instagram" value="{{ old('instagram', $user->instagram) }}" placeholder="Kullanıcı adı"></span></label>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">Telegram:</span><span class="setting__list-value"><input type="text" name="telegram" value="{{ old('telegram', $user->telegram) }}" placeholder="Kullanıcı adı"></span></label>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">Threads:</span><span class="setting__list-value"><input type="text" name="threads" value="{{ old('threads', $user->threads) }}" placeholder="Kullanıcı adı"></span></label>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">YouTube:</span><span class="setting__list-value"><input type="text" name="youtube" value="{{ old('youtube', $user->youtube) }}" placeholder="Kullanıcı adı"></span></label>
                                <p class="setting__list-item"><span></span><span class="small color-gray">Sosyal medya alanlarına yalnızca kullanıcı adınızı yazın.</span></p>
                            </section>
                            <div class="setting__submit"><button class="btn btn-primary" type="submit">Kaydet</button></div>
                        </form>
                    </section>

                    <section
                        class="tabs-panel {{ $activeTab === 'site' ? 'is-active' : '' }}"
                        id="settings-site-pane"
                        data-tab-panel="site"
                        role="tabpanel"
                        aria-labelledby="settings-site-tab"
                        tabindex="0"
                        @if ($activeTab !== 'site') hidden @endif
                    >
                        <h1 class="h3">Tercihler</h1>
                        <form class="setting__box setting__list" action="{{ route('settings.preferences.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="activeTab" value="site">
                            <label class="setting__list-item setting__list-item--center">
                                <span class="setting__list-label">Saat dilimi</span>
                                <span class="setting__list-value">
                                    <select class="form-select" name="timezone">
                                        @foreach ($timezones as $timezone)
                                            <option value="{{ $timezone }}" @selected(old('timezone', $user->timezone) === $timezone)>{{ $timezone }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </label>
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="notify_posts" value="1" @checked(old('notify_posts', $user->notify_posts))> <span>Yeni yazılar hakkında e-posta gönder</span></label>
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="notify_replies" value="1" @checked(old('notify_replies', $user->notify_replies))> <span>Yanıtlar hakkında bildirim gönder</span></label>
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="notify_announcements" value="1" @checked(old('notify_announcements', $user->notify_announcements))> <span>Site duyurularını gönder</span></label>
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="show_adult_content" value="1" @checked(old('show_adult_content', $user->show_adult_content))> <span>18+ içerikleri göster</span></label>
                            <div><button class="btn btn-primary" type="submit">Kaydet</button></div>
                        </form>
                    </section>

                    <section
                        class="tabs-panel {{ $activeTab === 'security' ? 'is-active' : '' }}"
                        id="settings-security-pane"
                        data-tab-panel="security"
                        role="tabpanel"
                        aria-labelledby="settings-security-tab"
                        tabindex="0"
                        @if ($activeTab !== 'security') hidden @endif
                    >
                        <h1 class="h3">Güvenlik</h1>
                        <div class="setting__box setting__list">
                            <form class="setting__list" action="{{ route('settings.password.update') }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="activeTab" value="security">
                                <h2 class="h5">Şifre değiştir</h2>
                                <input type="password" name="current_password" placeholder="Mevcut Şifre" autocomplete="current-password" required>
                                <input type="password" name="password" placeholder="Yeni Şifre" autocomplete="new-password" minlength="12" required>
                                <input type="password" name="password_confirmation" placeholder="Yeni şifreyi tekrar" autocomplete="new-password" required>
                                <div><button class="btn btn-primary" type="submit">Şifreyi güncelle</button></div>
                            </form>

                            <form class="setting__list" action="{{ route('settings.email.update') }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="activeTab" value="security">
                                <h2 class="h5">E-posta</h2>
                                <input type="email" name="email" value="{{ $user->email }}" autocomplete="email" required>
                                <input type="password" name="current_password" placeholder="Şifrenizi doğrulayın" autocomplete="current-password" required>
                                <div><button class="btn btn-primary" type="submit">E-postayı güncelle</button></div>
                            </form>

                            <section class="setting__list">
                                <h2 class="h5">İki faktörlü kimlik doğrulama</h2>
                                @if ($user->two_factor_confirmed_at)
                                    <p class="color-green">İki faktörlü doğrulama hesabınızda etkin.</p>
                                    <p class="small color-gray">Giriş yaparken Authenticator uygulamanızdaki 6 haneli kod istenir. Her kurtarma kodu yalnızca bir kez kullanılabilir.</p>
                                    @if (session('twoFactorRecoveryCodes'))
                                        <div class="card card-body">
                                            <strong>Kurtarma kodlarınız</strong>
                                            <p class="small color-gray">Bu kodları şimdi kaydedin. Bu liste tekrar gösterilmeyecek.</p>
                                            <ul>
                                                @foreach (session('twoFactorRecoveryCodes') as $recoveryCode)
                                                    <li><code>{{ $recoveryCode }}</code></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <form class="setting__list" action="{{ route('settings.two-factor.disable') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <label>Şifreniz <input type="password" name="current_password" autocomplete="current-password" required></label>
                                        <label>Authenticator veya kurtarma kodu <input type="text" name="code" autocomplete="one-time-code" required></label>
                                        <button class="btn btn-secondary" type="submit">İki faktörlü doğrulamayı kapat</button>
                                    </form>
                                @elseif ($user->two_factor_secret)
                                    <p>1. Google Authenticator, Microsoft Authenticator veya 1Password uygulamasını açın.</p>
                                    <p>2. Hesabı manuel ekleyin ve aşağıdaki anahtarı girin:</p>
                                    <p><code style="overflow-wrap:anywhere">{{ $user->two_factor_secret }}</code></p>
                                    <p class="small color-gray">Hesap: {{ $user->email }} · Tür: Zaman tabanlı (TOTP) · 6 hane · 30 saniye</p>
                                    <form class="setting__list" action="{{ route('settings.two-factor.confirm') }}" method="POST">
                                        @csrf
                                        <label>Uygulamadaki 6 haneli kod <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required></label>
                                        <button class="btn btn-primary" type="submit">Kodu doğrula ve etkinleştir</button>
                                    </form>
                                @else
                                    <p class="small color-gray">Hesabınıza girişte şifrenize ek olarak Authenticator uygulamanızdaki tek kullanımlık kod sorulur.</p>
                                    <button class="btn btn-primary" type="button" data-modal-btn="two-factor-setup" aria-controls="twoFactorSetupModal">İki faktörlü doğrulamayı kur</button>
                                @endif
                            </section>

                            <section class="setting__list">
                                <h2 class="h5">Sosyal ağlar</h2>
                                <div class="setting__social">
                                    <span class="btn btn-secondary" aria-label="Google hesabı bağlantısı henüz desteklenmiyor">
                                        <x-repost-icon name="google" size="20" />Google
                                    </span>
                                    <span class="btn btn-secondary" aria-label="Yandex hesabı bağlantısı henüz desteklenmiyor">
                                        <x-repost-icon name="yandex" size="20" />Yandex
                                    </span>
                                </div>
                            </section>
                        </div>
                    </section>

                    <section
                        class="tabs-panel {{ $activeTab === 'blacklist' ? 'is-active' : '' }}"
                        id="settings-blacklist-pane"
                        data-tab-panel="blacklist"
                        role="tabpanel"
                        aria-labelledby="settings-blacklist-tab"
                        tabindex="0"
                        @if ($activeTab !== 'blacklist') hidden @endif
                    >
                        <h1 class="h3">Engellenen kullanıcılar</h1>
                        <div class="setting__box">
                            <div class="setting__ignorlist">
                                @forelse ($blockedUsers as $blockedUser)
                                    <span class="ignore-list-item">
                                        @if (filled($blockedUser->username))
                                            <a class="pm_list" href="{{ route('profiles.show', $blockedUser) }}">{{ $blockedUser->displayName() }}</a>
                                        @else
                                            <span class="pm_list">{{ $blockedUser->displayName() }}</span>
                                        @endif
                                        <form action="{{ route('settings.blocked-users.destroy', $blockedUser) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="activeTab" value="blacklist">
                                            <button class="btn btn-red-ghost btn-circle btn-sm" type="submit" aria-label="Engeli kaldır"><x-repost-icon name="close" size="14" /></button>
                                        </form>
                                    </span>
                                @empty
                                    <p class="color-gray">Engellediğiniz kullanıcı yok.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    <section
                        class="tabs-panel {{ $activeTab === 'verify' ? 'is-active' : '' }}"
                        id="settings-verify-pane"
                        data-tab-panel="verify"
                        role="tabpanel"
                        aria-labelledby="settings-verify-tab"
                        tabindex="0"
                        @if ($activeTab !== 'verify') hidden @endif
                    >
                        <h1 class="h3">Mavi tik doğrulaması</h1>
                        <div class="setting__box">
                            <section class="setting__list">
                                @if ($user->hasVerifiedBadge())
                                    <p>Hesabınız başarıyla doğrulandı.</p>
                                    <span class="verify" role="img" aria-label="Doğrulanmış hesap"><x-repost-icon name="verify" size="18" /></span>
                                @elseif ($user->verification_status === 'pending')
                                    <p>Doğrulama başvurunuz inceleniyor.</p>
                                    <p class="small color-gray">Başvuru {{ $user->verification_requested_at?->diffForHumans() }} gönderildi.</p>
                                    <form action="{{ route('settings.verification.cancel') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="activeTab" value="verify">
                                        <button class="btn btn-secondary" type="submit">Başvuruyu iptal et</button>
                                    </form>
                                @else
                                    @if ($user->verification_status === 'rejected')
                                        <p>Önceki başvurunuz onaylanmadı. Yeni bir başvuru gönderebilirsiniz.</p>
                                    @endif
                                    <p>Mavi tik doğrulaması için başvurun. Hesabınızın bağımsız olarak nasıl doğrulanabileceğini açıklayın. Her başvuru bir yönetici tarafından incelenir.</p>
                                    <form class="setting__list" action="{{ route('settings.verification.request') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="activeTab" value="verify">
                                        <label for="verification_reason">Doğrulama bilgileri</label>
                                        <textarea id="verification_reason" name="verification_reason" rows="5" minlength="20" maxlength="2000" required>{{ old('verification_reason', $user->verification_status === 'rejected' ? $user->verification_reason : '') }}</textarea>
                                        <button class="btn btn-primary" type="submit">Doğrulama başvurusu gönder</button>
                                    </form>
                                @endif
                            </section>
                        </div>
                    </section>

                    <section
                        class="tabs-panel {{ $activeTab === 'delete' ? 'is-active' : '' }}"
                        id="settings-delete-pane"
                        data-tab-panel="delete"
                        role="tabpanel"
                        aria-labelledby="settings-delete-tab"
                        tabindex="0"
                        @if ($activeTab !== 'delete') hidden @endif
                    >
                        <h1 class="h3">Hesabı sil</h1>
                        <form class="setting__box setting__list" action="{{ route('settings.account.destroy') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="activeTab" value="delete">
                            <p>Hesabınız ve yorumlarınız kalıcı olarak silinir. Bu işlem geri alınamaz.</p>
                            <input type="password" name="current_password" placeholder="Şifrenizi doğrulayın" autocomplete="current-password" required>
                            <div><button class="btn btn-red" type="submit">Hesabı sil</button></div>
                        </form>
                    </section>
                </div>
            </div>
        </div>

        <aside class="setting__aside">
            <div class="widgets">
                <section class="widget card card-body setting__menu">
                    <div class="widget__content widget__content--bleed">
                        <ul class="menu" aria-label="Ayar bölümleri" data-tabs-nav="settings">
                            <li class="menu__profile-item">
                                @if (filled($user->username))
                                    <a href="{{ route('profiles.show', $user) }}" class="menu__profile user-box">
                                @else
                                    <div class="menu__profile user-box">
                                @endif
                                    <span class="avatar"><img src="{{ $user->avatarUrl() }}" alt="" width="40" height="40"></span>
                                    <span class="user-box-content">
                                        <span class="name">
                                            {{ $user->displayName() }}
                                            @if ($user->hasVerifiedBadge())<span class="verify"><x-repost-icon name="verify" size="14" /></span>@endif
                                        </span>
                                        <span class="meta">{{ filled($user->username) ? 'Profile geri dön' : 'Profil ayarlarından kullanıcı adı belirleyin' }}</span>
                                    </span>
                                @if (filled($user->username))
                                    </a>
                                @else
                                    </div>
                                @endif
                            </li>
                            <li class="menu__divider" aria-hidden="true"></li>
                            @foreach ([
                                'profile' => ['Profil', 'user'],
                                'site' => ['Tercihler', 'compass'],
                                'security' => ['Güvenlik', 'security'],
                                'blacklist' => ['Engellenenler', 'list'],
                                'verify' => ['Mavi tik doğrulaması', 'comment'],
                                'delete' => ['Hesabı sil', 'trash'],
                            ] as $tab => [$label, $icon])
                                <li class="menu__item {{ $tab === 'delete' ? 'menu__item--danger' : '' }}">
                                    <button
                                        class="menu__link {{ $activeTab === $tab ? 'is-active' : '' }}"
                                        id="settings-{{ $tab }}-tab"
                                        type="button"
                                        data-tab="{{ $tab }}"
                                        aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}"
                                        aria-label="{{ $label }}"
                                        title="{{ $label }}"
                                    >
                                        <x-repost-icon name="{{ $icon }}" size="20" class="menu__icon {{ $tab === 'delete' ? 'color-red' : 'color-gray' }}" />
                                        <span>{{ $label }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
                <div class="setting__aside-footer">
                    @include('layouts._footer')
                </div>
            </div>
        </aside>
    </div>
    <div class="modal" id="changeUsernameModal" data-modal="change-username" role="dialog" aria-modal="true" aria-labelledby="changeUsernameModalLabel" aria-hidden="true">
        <div class="modal-content">
            <button type="button" class="btn btn-secondary btn-md btn-circle modal-close" data-modal-close aria-label="Kapat">
                <x-repost-icon name="close" size="16" />
            </button>
            <header class="modal-header">
                <h2 class="modal-title" id="changeUsernameModalLabel">Kullanıcı adını değiştir</h2>
            </header>
            <div class="modal-body">Kullanıcı adını ayda en fazla bir kez değiştirebilirsin. Değişiklik talebinde bulunmak için destek ekibiyle iletişime geçin.</div>
            <div class="modal-footer">
                <a href="{{ route('contacts') }}" class="btn btn-primary">Destek ile iletişime geçin.</a>
            </div>
        </div>
    </div>
    <div class="modal" id="twoFactorSetupModal" data-modal="two-factor-setup" role="dialog" aria-modal="true" aria-labelledby="twoFactorSetupModalLabel" aria-hidden="true">
        <div class="modal-content">
            <button type="button" class="btn btn-secondary btn-md btn-circle modal-close" data-modal-close aria-label="Kapat">
                <x-repost-icon name="close" size="16" />
            </button>
            <header class="modal-header">
                <h2 class="modal-title" id="twoFactorSetupModalLabel">İki faktörlü doğrulama</h2>
            </header>
            <div class="modal-body">
                İki faktörlü doğrulamayı etkinleştirdiğinizde hesabınıza giriş yaparken şifrenize ek olarak Authenticator uygulamanızdaki tek kullanımlık kod istenir.
            </div>
            <div class="modal-footer">
                <form action="{{ route('settings.two-factor.start') }}" method="POST">
                    @csrf
                    <button class="btn btn-primary" type="submit">Kuruluma başla</button>
                </form>
            </div>
        </div>
    </div>
@endsection
