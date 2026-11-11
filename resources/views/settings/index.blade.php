@extends('layouts.app')

@section('title', 'Ayarlar')

@push('head')
    <style>
        .notification-preferences{margin-top:22px;padding:18px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}
        .notification-preferences__intro h2{margin:0 0 4px;font-size:16px;font-weight:750}
        .notification-preferences__intro p,.notification-preferences__note{margin:0;color:#718096;font-size:12px}
        .notification-preferences__tabs{display:flex;gap:4px;margin:16px 0 8px;padding:4px;border-radius:999px;background:#eef1f6}
        .notification-preferences__tabs button{flex:1;padding:9px 12px;border:0;border-radius:999px;background:transparent;color:#64748b;font-size:14px;font-weight:700;cursor:pointer;transition:background .15s,color .15s,box-shadow .15s}
        .notification-preferences__tabs button.is-active{background:#fff;color:#111827;box-shadow:0 2px 7px rgba(15,23,42,.10)}
        .notification-preferences__panel{display:block}
        .notification-preferences__panel[hidden]{display:none!important}
        .notification-preference{display:flex;align-items:center;gap:12px;padding:12px 2px;border-bottom:1px solid #edf0f4;cursor:pointer}
        .notification-preference__icon{display:grid;place-items:center;flex:none;width:32px;height:32px;border-radius:9px;background:#eff6ff;color:#3985f6}
        .notification-preference__icon svg{display:block;stroke:currentColor}
        .notification-preference__text{display:flex;flex:1;min-width:0;flex-direction:column;gap:3px}
        .notification-preference__text strong{font-size:12px;font-weight:700}
        .notification-preference__text small{color:#718096;font-size:11px;line-height:1.35}
        .notification-preference__switch{appearance:none;position:relative;flex:none;width:36px;height:21px;margin:0;border:0;border-radius:20px;background:#cbd5e1;cursor:pointer;transition:background .15s}
        .notification-preference__switch:before{content:"";position:absolute;top:3px;left:3px;width:15px;height:15px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.18);transition:transform .15s}
        .notification-preference__switch:checked{background:#2563eb}
        .notification-preference__switch:checked:before{transform:translateX(15px)}
        .notification-channel-heading,.notification-channel-row{display:grid;grid-template-columns:32px minmax(110px,1fr) 62px 52px;align-items:center;gap:10px}
        .notification-channel-heading{padding:10px 2px;color:#718096;font-size:11px;text-align:center}
        .notification-channel-heading span:nth-child(2){text-align:left}
        .notification-channel-heading strong{font-weight:700}
        .notification-channel-row{padding:12px 2px;border-bottom:1px solid #edf0f4}
        .notification-channel-switch{display:flex;align-items:center;flex-direction:column;gap:7px;color:#718096;font-size:10px;cursor:pointer}
        .notification-preferences__note{margin-top:12px;line-height:1.5}
        .notification-push-permission{display:flex;align-items:center;flex-wrap:wrap;gap:10px;margin-top:14px}
        .notification-push-permission .btn{flex:none}
        .notification-push-permission [data-browser-notification-status]{margin:0}
        @media(max-width:600px){.notification-preferences{padding:13px}.notification-preference{gap:9px}.notification-channel-heading,.notification-channel-row{grid-template-columns:28px minmax(90px,1fr) 48px 42px;gap:6px}.notification-channel-row .notification-preference__text strong{font-size:11px}.notification-channel-row .notification-preference__text small{font-size:10px}}
        /* Repost mobile settings layout: keep the settings menu, hide its desktop footer. */
        @media screen and (max-width: 920px) {
            html body .setting__aside-footer {
                display: none !important;
            }
        }
    </style>


@endpush

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
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="show_adult_content" value="1" @checked(old('show_adult_content', $user->show_adult_content))> <span>18+ içerikleri göster</span></label>
                            <div class="notification-preferences">
                                <div class="notification-preferences__intro">
                                    <h2>Bildirim tercihleri</h2>
                                    <p>Bildirim akışını ve gönderim kanallarını ayrı ayrı yönet.</p>
                                </div>
                                <div class="notification-preferences__tabs" role="tablist" aria-label="Bildirim tercihleri">
                                    <button type="button" class="is-active" data-notification-view="stream" role="tab" aria-selected="true">Akış</button>
                                    <button type="button" data-notification-view="channels" role="tab" aria-selected="false">Kanallar</button>
                                </div>
                                <div class="notification-preferences__panel" data-notification-view-panel="stream" role="tabpanel">
                                    <label class="notification-preference">
                                        <span class="notification-preference__icon"><x-repost-icon name="comment" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Yanıtlar</strong><small>Yazılarına ve yorumlarına gelen yanıtlar</small></span>
                                        <input type="hidden" name="notification_preferences[stream][replies]" value="0">
                                        <input class="notification-preference__switch" type="checkbox" name="notification_preferences[stream][replies]" value="1" @checked(old('notification_preferences.stream.replies', data_get($notificationPreferences, 'stream.replies', true)))>
                                    </label>
                                    <label class="notification-preference">
                                        <span class="notification-preference__icon"><x-repost-icon name="feed" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Yeni yazılar</strong><small>Takip ettiğin kanallardaki yeni yazılar</small></span>
                                        <input type="hidden" name="notification_preferences[stream][posts]" value="0">
                                        <input class="notification-preference__switch" type="checkbox" name="notification_preferences[stream][posts]" value="1" @checked(old('notification_preferences.stream.posts', data_get($notificationPreferences, 'stream.posts', true)))>
                                    </label>
                                    <label class="notification-preference">
                                        <span class="notification-preference__icon"><x-repost-icon name="info" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Duyurular</strong><small>OgraFi duyuruları ve önemli güncellemeler</small></span>
                                        <input type="hidden" name="notification_preferences[stream][announcements]" value="0">
                                        <input class="notification-preference__switch" type="checkbox" name="notification_preferences[stream][announcements]" value="1" @checked(old('notification_preferences.stream.announcements', data_get($notificationPreferences, 'stream.announcements', true)))>
                                    </label>
                                    <label class="notification-preference">
                                        <span class="notification-preference__icon"><x-repost-icon name="user" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Yeni takipçiler</strong><small>Seni takip etmeye başlayan kişiler</small></span>
                                        <input type="hidden" name="notification_preferences[stream][followers]" value="0">
                                        <input class="notification-preference__switch" type="checkbox" name="notification_preferences[stream][followers]" value="1" @checked(old('notification_preferences.stream.followers', data_get($notificationPreferences, 'stream.followers', true)))>
                                    </label>
                                    <label class="notification-preference">
                                        <span class="notification-preference__icon"><x-repost-icon name="message" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Mesajlar</strong><small>Yeni özel mesajlar</small></span>
                                        <input type="hidden" name="notification_preferences[stream][messages]" value="0">
                                        <input class="notification-preference__switch" type="checkbox" name="notification_preferences[stream][messages]" value="1" @checked(old('notification_preferences.stream.messages', data_get($notificationPreferences, 'stream.messages', true)))>
                                    </label>
                                    <label class="notification-preference">
                                        <span class="notification-preference__icon"><x-repost-icon name="tags" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Bahsetmeler</strong><small>Birisi senden bahsettiğinde</small></span>
                                        <input type="hidden" name="notification_preferences[stream][mentions]" value="0">
                                        <input class="notification-preference__switch" type="checkbox" name="notification_preferences[stream][mentions]" value="1" @checked(old('notification_preferences.stream.mentions', data_get($notificationPreferences, 'stream.mentions', true)))>
                                    </label>
                                    <label class="notification-preference">
                                        <span class="notification-preference__icon"><x-repost-icon name="security" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Güvenlik bildirimleri</strong><small>Hesap ve güvenlik hareketleri</small></span>
                                        <input type="hidden" name="notification_preferences[stream][security]" value="0">
                                        <input class="notification-preference__switch" type="checkbox" name="notification_preferences[stream][security]" value="1" @checked(old('notification_preferences.stream.security', data_get($notificationPreferences, 'stream.security', true)))>
                                    </label>
                                </div>
                                <div class="notification-preferences__panel" data-notification-view-panel="channels" hidden role="tabpanel">
                                    <div class="notification-channel-heading"><span></span><span>Bildirim türü</span><strong>E-posta</strong><strong>Push</strong></div>
                                    <div class="notification-channel-row">
                                        <span class="notification-preference__icon"><x-repost-icon name="comment" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Yanıtlar</strong><small>Yazılarına ve yorumlarına gelen yanıtlar</small></span>
                                        <label class="notification-channel-switch"><span>E-posta</span>
                                            <input type="hidden" name="notification_preferences[email][replies]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[email][replies]" value="1" @checked(old('notification_preferences.email.replies', data_get($notificationPreferences, 'email.replies', true)))>
                                        </label>
                                        <label class="notification-channel-switch"><span>Push</span>
                                            <input type="hidden" name="notification_preferences[push][replies]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[push][replies]" value="1" @checked(old('notification_preferences.push.replies', data_get($notificationPreferences, 'push.replies', false)))>
                                        </label>
                                    </div>
                                    <div class="notification-channel-row">
                                        <span class="notification-preference__icon"><x-repost-icon name="feed" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Yeni yazılar</strong><small>Takip ettiğin kanallardaki yeni yazılar</small></span>
                                        <label class="notification-channel-switch"><span>E-posta</span>
                                            <input type="hidden" name="notification_preferences[email][posts]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[email][posts]" value="1" @checked(old('notification_preferences.email.posts', data_get($notificationPreferences, 'email.posts', true)))>
                                        </label>
                                        <label class="notification-channel-switch"><span>Push</span>
                                            <input type="hidden" name="notification_preferences[push][posts]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[push][posts]" value="1" @checked(old('notification_preferences.push.posts', data_get($notificationPreferences, 'push.posts', false)))>
                                        </label>
                                    </div>
                                    <div class="notification-channel-row">
                                        <span class="notification-preference__icon"><x-repost-icon name="info" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Duyurular</strong><small>OgraFi duyuruları ve önemli güncellemeler</small></span>
                                        <label class="notification-channel-switch"><span>E-posta</span>
                                            <input type="hidden" name="notification_preferences[email][announcements]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[email][announcements]" value="1" @checked(old('notification_preferences.email.announcements', data_get($notificationPreferences, 'email.announcements', true)))>
                                        </label>
                                        <label class="notification-channel-switch"><span>Push</span>
                                            <input type="hidden" name="notification_preferences[push][announcements]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[push][announcements]" value="1" @checked(old('notification_preferences.push.announcements', data_get($notificationPreferences, 'push.announcements', false)))>
                                        </label>
                                    </div>
                                    <div class="notification-channel-row">
                                        <span class="notification-preference__icon"><x-repost-icon name="user" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Yeni takipçiler</strong><small>Seni takip etmeye başlayan kişiler</small></span>
                                        <label class="notification-channel-switch"><span>E-posta</span>
                                            <input type="hidden" name="notification_preferences[email][followers]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[email][followers]" value="1" @checked(old('notification_preferences.email.followers', data_get($notificationPreferences, 'email.followers', true)))>
                                        </label>
                                        <label class="notification-channel-switch"><span>Push</span>
                                            <input type="hidden" name="notification_preferences[push][followers]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[push][followers]" value="1" @checked(old('notification_preferences.push.followers', data_get($notificationPreferences, 'push.followers', false)))>
                                        </label>
                                    </div>
                                    <div class="notification-channel-row">
                                        <span class="notification-preference__icon"><x-repost-icon name="message" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Mesajlar</strong><small>Yeni özel mesajlar</small></span>
                                        <label class="notification-channel-switch"><span>E-posta</span>
                                            <input type="hidden" name="notification_preferences[email][messages]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[email][messages]" value="1" @checked(old('notification_preferences.email.messages', data_get($notificationPreferences, 'email.messages', true)))>
                                        </label>
                                        <label class="notification-channel-switch"><span>Push</span>
                                            <input type="hidden" name="notification_preferences[push][messages]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[push][messages]" value="1" @checked(old('notification_preferences.push.messages', data_get($notificationPreferences, 'push.messages', false)))>
                                        </label>
                                    </div>
                                    <div class="notification-channel-row">
                                        <span class="notification-preference__icon"><x-repost-icon name="tags" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Bahsetmeler</strong><small>Birisi senden bahsettiğinde</small></span>
                                        <label class="notification-channel-switch"><span>E-posta</span>
                                            <input type="hidden" name="notification_preferences[email][mentions]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[email][mentions]" value="1" @checked(old('notification_preferences.email.mentions', data_get($notificationPreferences, 'email.mentions', true)))>
                                        </label>
                                        <label class="notification-channel-switch"><span>Push</span>
                                            <input type="hidden" name="notification_preferences[push][mentions]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[push][mentions]" value="1" @checked(old('notification_preferences.push.mentions', data_get($notificationPreferences, 'push.mentions', false)))>
                                        </label>
                                    </div>
                                    <div class="notification-channel-row">
                                        <span class="notification-preference__icon"><x-repost-icon name="security" size="19" /></span>
                                        <span class="notification-preference__text"><strong>Güvenlik bildirimleri</strong><small>Hesap ve güvenlik hareketleri</small></span>
                                        <label class="notification-channel-switch"><span>E-posta</span>
                                            <input type="hidden" name="notification_preferences[email][security]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[email][security]" value="1" @checked(old('notification_preferences.email.security', data_get($notificationPreferences, 'email.security', true)))>
                                        </label>
                                        <label class="notification-channel-switch"><span>Push</span>
                                            <input type="hidden" name="notification_preferences[push][security]" value="0">
                                            <input class="notification-preference__switch" type="checkbox" name="notification_preferences[push][security]" value="1" @checked(old('notification_preferences.push.security', data_get($notificationPreferences, 'push.security', true)))>
                                        </label>
                                    </div>
                                    <div class="notification-push-permission">
                                        <button type="button" class="btn btn-secondary btn-sm" data-enable-browser-notifications>Tarayıcı bildirimlerini etkinleştir</button>
                                        <span class="notification-preferences__note" data-browser-notification-status>Push bildirimleri, OgraFi açıkken tarayıcı izniyle gösterilir.</span>
                                    </div>
                                </div>
                            </div>
                            <div><button class="btn btn-primary" type="submit">Kaydet</button></div>
                        </form>
                    </section>

                    <section
                        class="tabs-panel {{ $activeTab === 'cookies' ? 'is-active' : '' }}"
                        id="settings-cookies-pane"
                        data-tab-panel="cookies"
                        role="tabpanel"
                        aria-labelledby="settings-cookies-tab"
                        tabindex="0"
                        @if ($activeTab !== 'cookies') hidden @endif
                    >
                        <h1 class="h3">Çerez ayarları</h1>
                        <div class="setting__box setting__list">
                            <p class="color-gray">OgraFi'nin düzgün çalışması için zorunlu çerezler her zaman etkindir. Diğer çerez kategorilerini aşağıdan yönetebilirsin.</p>
                            <div class="setting__list-item setting__list-item--center">
                                <span class="setting__list-label"><strong>Zorunlu çerezler</strong><small class="d-block color-gray">Oturum, güvenlik ve temel site işlevleri için gereklidir. Kapatılamaz.</small></span>
                                <span class="setting__list-value"><input type="checkbox" checked disabled aria-label="Zorunlu çerezler her zaman açık"></span>
                            </div>
                            <label class="setting__list-item setting__list-item--center">
                                <span class="setting__list-label"><strong>Tercih çerezleri</strong><small class="d-block color-gray">Tema ve benzeri seçimlerini hatırlamamıza yardımcı olur.</small></span>
                                <span class="setting__list-value"><input type="checkbox" data-settings-cookie-category="preferences"></span>
                            </label>
                            <label class="setting__list-item setting__list-item--center">
                                <span class="setting__list-label"><strong>Analitik çerezler</strong><small class="d-block color-gray">Site kullanımını ölçerek OgraFi'yi geliştirmemize yardımcı olur.</small></span>
                                <span class="setting__list-value"><input type="checkbox" data-settings-cookie-category="analytics"></span>
                            </label>
                            <label class="setting__list-item setting__list-item--center">
                                <span class="setting__list-label"><strong>Pazarlama çerezleri</strong><small class="d-block color-gray">Reklam performansını ölçmek ve ilgili içerikler sunmak için kullanılır.</small></span>
                                <span class="setting__list-value"><input type="checkbox" data-settings-cookie-category="marketing"></span>
                            </label>
                            <div class="setting__submit" style="display:flex;gap:8px;flex-wrap:wrap">
                                <button class="btn btn-secondary" type="button" data-settings-cookie-reject>İsteğe bağlıları reddet</button>
                                <button class="btn btn-primary" type="button" data-settings-cookie-save>Seçimleri kaydet</button>
                                <button class="btn btn-primary" type="button" data-settings-cookie-accept>Hepsini kabul et</button>
                            </div>
                        </div>
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
                                @else
                                    <p class="small color-gray">
                                        @if ($user->two_factor_secret)
                                            İki faktörlü doğrulama kurulumunuz beklemede. Devam etmek için kurulumu popup üzerinden açın.
                                        @else
                                            Hesabınıza girişte şifrenize ek olarak Authenticator uygulamanızdaki tek kullanımlık kod sorulur.
                                        @endif
                                    </p>
                                    <div>
                                        <button class="btn btn-primary" type="button" data-modal-btn="two-factor-setup" aria-controls="twoFactorSetupModal">
                                            {{ $user->two_factor_secret ? 'Kuruluma devam et' : 'İki faktörlü doğrulamayı kur' }}
                                        </button>
                                    </div>
                                @endif
                            </section>

                            <section class="setting__list">
                                <h2 class="h5">Sosyal ağlar</h2>
                                <div class="setting__social">
                                    @foreach (['google' => 'Google', 'yandex' => 'Yandex'] as $provider => $socialProvider)
                                        @php($connectedAccount = $socialAccounts->get($provider))
                                        @if ($connectedAccount)
                                            <button type="button" class="btn btn-secondary" data-modal-btn="social-unlink-{{ $provider }}" aria-controls="socialUnlink{{ ucfirst($provider) }}Modal">
                                                @if (filled($connectedAccount->provider_avatar_url))
                                                    <img src="{{ $connectedAccount->provider_avatar_url }}" alt="" width="20" height="20" class="settings-social-avatar" referrerpolicy="no-referrer">
                                                @else
                                                    <x-repost-icon :name="$provider" size="20" />
                                                @endif
                                                {{ $socialProvider }}
                                            </button>
                                        @elseif ($provider === 'google' ? $googleLoginAvailable : $yandexLoginAvailable)
                                            <button type="button" class="btn btn-secondary" data-modal-btn="social-link-{{ $provider }}" aria-controls="socialLink{{ ucfirst($provider) }}Modal">
                                                <x-repost-icon :name="$provider" size="20" />{{ $socialProvider }}
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-secondary" disabled>
                                                <x-repost-icon :name="$provider" size="20" />{{ $socialProvider }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </section>
                            @foreach (['google' => 'Google', 'yandex' => 'Yandex'] as $provider => $socialProvider)
                                @php($connectedAccount = $socialAccounts->get($provider))
                                @if ($connectedAccount)
                                    <div class="modal" id="socialUnlink{{ ucfirst($provider) }}Modal" data-modal="social-unlink-{{ $provider }}" role="dialog" aria-modal="true" aria-labelledby="socialUnlink{{ ucfirst($provider) }}Title" aria-hidden="true">
                                        <div class="modal-content">
                                            <button type="button" class="btn btn-secondary btn-md btn-circle modal-close" data-modal-close aria-label="Kapat"><x-repost-icon name="close" size="16" /></button>
                                            <header class="modal-header"><h2 class="modal-title" id="socialUnlink{{ ucfirst($provider) }}Title">{{ $socialProvider }} bağlantısını kes</h2></header>
                                            <div class="modal-body"><p>{{ $socialProvider }} hesabınızın OgraFi bağlantısı kaldırılacak. Devam etmek istiyor musunuz?</p></div>
                                            <footer class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-modal-close>İptal</button>
                                                <form method="POST" action="{{ route('oauth.unlink', ['provider' => $provider]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-primary" type="submit">Onayla</button>
                                                </form>
                                            </footer>
                                        </div>
                                    </div>
                                @elseif ($provider === 'google' ? $googleLoginAvailable : $yandexLoginAvailable)
                                    <div class="modal" id="socialLink{{ ucfirst($provider) }}Modal" data-modal="social-link-{{ $provider }}" role="dialog" aria-modal="true" aria-labelledby="socialLink{{ ucfirst($provider) }}Title" aria-hidden="true">
                                        <div class="modal-content">
                                            <button type="button" class="btn btn-secondary btn-md btn-circle modal-close" data-modal-close aria-label="Kapat"><x-repost-icon name="close" size="16" /></button>
                                            <header class="modal-header"><h2 class="modal-title" id="socialLink{{ ucfirst($provider) }}Title">{{ $socialProvider }} hesabını bağla</h2></header>
                                            <div class="modal-body"><p>OgraFi hesabınızı {{ $socialProvider }} hesabınıza bağlamak için devam etmek istiyor musunuz?</p></div>
                                            <footer class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-modal-close>İptal</button>
                                                <a class="btn btn-primary" href="{{ route('oauth.link.redirect', ['provider' => $provider]) }}">Onayla</a>
                                            </footer>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
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
                        <h1 class="h3">{{ $user->is_company ? "Şirket doğrulaması" : "Mavi tik doğrulaması" }}</h1>
                        <div class="setting__box">
                            <section class="setting__list">
                                @if ($user->is_company && $user->hasVerifiedCompanyBadge())
                                    <p>Şirket hesabınız altın rozetle doğrulanmıştır.</p>
                                @elseif ($user->is_company)
                                    <p>Bu bir şirket hesabıdır. Altın şirket doğrulama rozeti yönetici incelemesiyle verilir.</p>
                                @elseif ($user->hasVerifiedBadge())
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
                            <p>Hesabınız hemen kapatılır ve profiliniz gizlenir. Kalıcı silme talebiniz yönetici tarafından incelenir; onaylandığında hesabınız ve ilişkili kayıtlarınız silinir.</p>
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
                                            @if ($user->hasVerifiedCompanyBadge())<x-company-verified-badge size="14" />@elseif ($user->hasVerifiedBadge())<span class="verify"><x-repost-icon name="verify" size="14" /></span>@endif
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
                            ] as $tab => [$label, $icon])
                                <li class="menu__item">
                                    <button
                                        class="menu__link {{ $activeTab === $tab ? 'is-active' : '' }}"
                                        id="settings-{{ $tab }}-tab"
                                        type="button"
                                        data-tab="{{ $tab }}"
                                        aria-selected="{{ $activeTab === $tab ? 'true' : 'false' }}"
                                        aria-label="{{ $label }}"
                                        title="{{ $label }}"
                                    >
                                        <x-repost-icon name="{{ $icon }}" size="20" class="menu__icon color-gray" />
                                        <span>{{ $label }}</span>
                                    </button>
                                </li>
                            @endforeach
                            <li class="menu__item">
                                <button
                                    class="menu__link {{ $activeTab === 'cookies' ? 'is-active' : '' }}"
                                    id="settings-cookies-tab"
                                    type="button"
                                    data-tab="cookies"
                                    aria-selected="{{ $activeTab === 'cookies' ? 'true' : 'false' }}"
                                    aria-label="Çerez ayarları"
                                    title="Çerez ayarları"
                                >
                                    <x-repost-icon name="setting" size="20" class="menu__icon color-gray" />
                                    <span>Çerez ayarları</span>
                                </button>
                            </li>
                            @foreach ([
                                'security' => ['Güvenlik', 'security'],
                                'blacklist' => ['Engellenenler', 'list'],
                                'verify' => [$user->is_company ? 'Şirket doğrulaması' : 'Mavi tik doğrulaması', 'comment'],
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
            <form action="{{ route('settings.username.update') }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <label for="settings-username">Kullanıcı adı</label>
                    <input id="settings-username" type="text" name="username" value="{{ old('username', $user->username) }}" maxlength="30" autocomplete="nickname" autocapitalize="none" spellcheck="false" required>
                    <p class="small color-gray">E-posta adresinden bağımsız, kendin belirle. En fazla 30 karakter ve başka bir kullanıcı tarafından alınmamış olmalı.</p>
                    @error('username')<p class="color-red" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Kullanıcı adını kaydet</button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal" id="twoFactorSetupModal" data-modal="two-factor-setup" role="dialog" aria-modal="true" aria-labelledby="twoFactorSetupModalLabel" aria-hidden="true">
        <div class="modal-content two-factor-modal">
            <button type="button" class="btn btn-secondary btn-md btn-circle modal-close" data-modal-close aria-label="Kapat">
                <x-repost-icon name="close" size="16" />
            </button>
            <header class="modal-header">
                <h2 class="modal-title" id="twoFactorSetupModalLabel">İki faktörlü doğrulama</h2>
            </header>
            <div class="modal-body">
                <div data-two-factor-step="loading" class="two-factor-step" hidden>
                    <p>Kurulum hazırlanıyor...</p>
                </div>
                <div data-two-factor-step="scan" class="two-factor-step">
                    <div class="two-factor-mark"><x-repost-icon name="security" size="28" /></div>
                    <h3>İki aşamalı doğrulamayı aç</h3>
                    <p class="two-factor-help">Authenticator uygulamanızı açın ve aşağıdaki QR kodunu tarayın.</p>
                    <div id="twoFactorQr" class="two-factor-qr" aria-label="Authenticator QR kodu"></div>
                    <button type="button" class="btn btn-primary two-factor-continue" data-two-factor-next>Devam et <span aria-hidden="true">→</span></button>
                    <div class="two-factor-divider"><span>veya kodu manuel girin</span></div>
                    <div class="two-factor-secret-row">
                        <code id="twoFactorSecret">•••• •••• ••••</code>
                        <button type="button" class="btn btn-secondary" data-copy-two-factor aria-label="Kurulum anahtarını kopyala"><x-repost-icon name="copy" size="18" /></button>
                    </div>
                    <p class="small color-gray" id="twoFactorAccount"></p>
                    <p class="two-factor-error" id="twoFactorStartError" role="alert" hidden></p>
                </div>
                <div data-two-factor-step="verify" class="two-factor-step" hidden>
                    <h3>Authenticator kodunu girin</h3>
                    <p class="two-factor-help">Uygulamanızda görünen 6 haneli kodu girerek kurulumu tamamlayın.</p>
                    <form id="twoFactorVerifyForm">
                        <label for="twoFactorCode">6 haneli doğrulama kodu</label>
                        <input id="twoFactorCode" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
                        <p class="two-factor-error" id="twoFactorError" role="alert" hidden></p>
                        <button class="btn btn-primary two-factor-continue" type="submit">Doğrula ve etkinleştir</button>
                    </form>
                    <button type="button" class="btn btn-link" data-two-factor-back>Geri dön</button>
                </div>
                <div data-two-factor-step="done" class="two-factor-step" hidden>
                    <h3>İki faktörlü doğrulama etkinleştirildi</h3>
                    <p class="two-factor-help">Kurtarma kodlarınızı güvenli bir yere kaydedin. Her kod yalnızca bir kez kullanılabilir.</p>
                    <ul id="twoFactorRecoveryCodes" class="two-factor-recovery-list"></ul>
                    <button type="button" class="btn btn-primary two-factor-continue" data-two-factor-finish>Tamam</button>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .setting__social .settings-social-avatar {
        display: inline-block;
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
        border-radius: 50%;
        object-fit: cover;
        vertical-align: middle;
    }
</style>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" referrerpolicy="no-referrer"></script>
<script>
(() => {
    const modal = document.getElementById('twoFactorSetupModal');
    if (!modal || modal.dataset.flowReady) return;
    modal.dataset.flowReady = 'true';

    const startUrl = @json(route('settings.two-factor.start'));
    const confirmUrl = @json(route('settings.two-factor.confirm'));
    const csrf = @json(csrf_token());
    const secretEl = document.getElementById('twoFactorSecret');
    const qrEl = document.getElementById('twoFactorQr');
    const errorEl = document.getElementById('twoFactorError');
    const startErrorEl = document.getElementById('twoFactorStartError');
    let secret = '';

    function showStep(name) {
        modal.querySelectorAll('[data-two-factor-step]').forEach((step) => {
            step.hidden = step.dataset.twoFactorStep !== name;
        });
        modal.querySelector('.modal-content')?.focus();
    }

    async function requestJson(url, body = null) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body ? JSON.stringify(body) : '{}'
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'İşlem tamamlanamadı. Tekrar deneyin.');
        return data;
    }

    modal.addEventListener('modal:open', async () => {
        showStep('loading');
        startErrorEl.hidden = true;
        errorEl.hidden = true;
        try {
            const data = await requestJson(startUrl);
            secret = data.secret;
            secretEl.textContent = secret;
            document.getElementById('twoFactorAccount').textContent = data.account;
            qrEl.innerHTML = '';
            if (window.QRCode) {
                new QRCode(qrEl, { text: data.otpauth_url, width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });
            } else {
                qrEl.textContent = 'QR kodu yüklenemedi. Manuel anahtarı kullanabilirsiniz.';
            }
            showStep('scan');
        } catch (error) {
            showStep('scan');
            startErrorEl.hidden = false;
            startErrorEl.textContent = error.message;
        }
    });

    modal.querySelector('[data-two-factor-next]')?.addEventListener('click', () => {
        errorEl.hidden = true;
        showStep('verify');
        document.getElementById('twoFactorCode').focus();
    });

    modal.querySelector('[data-two-factor-back]')?.addEventListener('click', () => showStep('scan'));

    modal.querySelector('[data-copy-two-factor]')?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(secret);
            const button = modal.querySelector('[data-copy-two-factor]');
            button.setAttribute('aria-label', 'Kopyalandı');
        } catch {
            secretEl.focus?.();
        }
    });

    document.getElementById('twoFactorVerifyForm')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorEl.hidden = true;
        const code = document.getElementById('twoFactorCode').value.trim();
        const submit = event.currentTarget.querySelector('[type="submit"]');
        submit.disabled = true;
        try {
            const data = await requestJson(confirmUrl, { code });
            const list = document.getElementById('twoFactorRecoveryCodes');
            list.innerHTML = '';
            data.recovery_codes.forEach((code) => {
                const item = document.createElement('li');
                const value = document.createElement('code');
                value.textContent = code;
                item.append(value);
                list.append(item);
            });
            showStep('done');
        } catch (error) {
            errorEl.hidden = false;
            errorEl.textContent = error.message;
        } finally {
            submit.disabled = false;
        }
    });

    modal.querySelector('[data-two-factor-finish]')?.addEventListener('click', () => window.location.reload());
})();
</script>

<script>
(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('choose_username') !== '1') return;
    document.querySelector('[data-modal-btn="change-username"]')?.click();
    document.getElementById('settings-username')?.focus();
})();
</script>
<script>
(() => {
    const panel = document.querySelector('[data-tab-panel="cookies"]');
    if (!panel) return;

    const keys = ['preferences', 'analytics', 'marketing'];
    const storageKey = 'ografi_cookie_consent_v1';
    const root = document.querySelector('[data-cookie-consent]');

    const readConsent = () => {
        try {
            const value = JSON.parse(localStorage.getItem(storageKey) || 'null');
            return value && value.version === 1 && value.categories ? value : null;
        } catch {
            return null;
        }
    };

    const syncFromStorage = () => {
        const consent = readConsent();
        keys.forEach((key) => {
            const input = panel.querySelector('[data-settings-cookie-category="' + key + '"]');
            if (input) input.checked = Boolean(consent?.categories?.[key]);
        });
    };

    const saveThroughConsentManager = (mode) => {
        if (!root) return;
        if (mode === 'accept') {
            root.querySelector('[data-cookie-accept]')?.click();
            syncFromStorage();
            return;
        }
        if (mode === 'reject') {
            root.querySelector('[data-cookie-reject]')?.click();
            syncFromStorage();
            return;
        }

        keys.forEach((key) => {
            const source = panel.querySelector('[data-settings-cookie-category="' + key + '"]');
            const target = root.querySelector('[data-cookie-category="' + key + '"]');
            if (source && target) target.checked = source.checked;
        });
        root.querySelector('[data-cookie-save]')?.click();
        syncFromStorage();
    };

    panel.querySelector('[data-settings-cookie-save]')?.addEventListener('click', () => saveThroughConsentManager('save'));
    panel.querySelector('[data-settings-cookie-accept]')?.addEventListener('click', () => saveThroughConsentManager('accept'));
    panel.querySelector('[data-settings-cookie-reject]')?.addEventListener('click', () => saveThroughConsentManager('reject'));

    syncFromStorage();
    window.addEventListener('storage', syncFromStorage);
    window.addEventListener('ografi:cookie-consent-updated', syncFromStorage);
})();
</script>
<script>
(() => {
    const tabs = document.querySelectorAll('[data-notification-view]');
    const panels = document.querySelectorAll('[data-notification-view-panel]');
    tabs.forEach((button) => {
        button.addEventListener('click', () => {
            const view = button.dataset.notificationView;
            tabs.forEach((tab) => {
                const active = tab === button;
                tab.classList.toggle('is-active', active);
                tab.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            panels.forEach((panel) => {
                panel.hidden = panel.dataset.notificationViewPanel !== view;
            });
        });
    });
})();
</script>
<script>
(() => {
    const button = document.querySelector('[data-enable-browser-notifications]');
    const status = document.querySelector('[data-browser-notification-status]');
    if (!button || !status) return;

    const refresh = () => {
        if (!('Notification' in window)) {
            status.textContent = 'Bu tarayıcı masaüstü bildirimlerini desteklemiyor.';
            button.disabled = true;
            return;
        }

        if (Notification.permission === 'granted') {
            status.textContent = 'Tarayıcı bildirim izni açık. Push tercihlerini etkinleştirebilirsin.';
            button.textContent = 'Bildirim izni etkin';
            button.disabled = true;
        } else if (Notification.permission === 'denied') {
            status.textContent = 'Bildirim izni tarayıcı tarafından engellendi. Site izinlerinden açmalısın.';
            button.textContent = 'Tarayıcı izni engellendi';
            button.disabled = true;
        }
    };

    button.addEventListener('click', async () => {
        if (!('Notification' in window)) {
            refresh();
            return;
        }

        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                status.textContent = 'İzin verildi. Push tercihleri açık olan yeni bildirimler masaüstünde gösterilecek.';
            } else {
                status.textContent = permission === 'denied'
                    ? 'İzin reddedildi. Tarayıcı site ayarlarından bildirim iznini aç.'
                    : 'Bildirim izni henüz verilmedi.';
            }
            refresh();
        } catch (error) {
            status.textContent = 'Bildirim izni alınamadı. Tarayıcı ayarlarını kontrol et.';
        }
    });

    refresh();
})();
</script>
@endpush
