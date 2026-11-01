@extends('layouts.app')

@section('title', 'Settings')

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
                        <h1 class="h3">Profile</h1>
                        <form class="setting__box" action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="activeTab" value="profile">
                            <section class="avatar-upload-field">
                                <div class="avatar__upload">
                                    <img class="avatar__upload-image" src="{{ $user->avatarUrl() }}" alt="" width="90" height="90">
                                    <input type="file" id="image" name="avatar" accept="image/jpeg,image/png,image/gif" aria-label="Upload avatar">
                                </div>
                                <div class="avatar-upload-field__content">
                                    <div class="avatar-upload-field__title">Upload avatar</div>
                                    <p class="avatar-upload-field__description">JPG, PNG or GIF. Maximum size: 2 MB.</p>
                                    <div class="checkbox setting__avatar-upload">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" name="delete_avatar" value="1">
                                            <span>Delete avatar</span>
                                        </label>
                                    </div>
                                </div>
                            </section>

                            <section>
                                <h2 class="h5">Information</h2>
                                <dl class="setting__list">
                                    <div class="setting__list-item"><dt class="setting__list-label">ID:</dt><dd class="setting__list-value">{{ $user->id }}</dd></div>
                                    <div class="setting__list-item"><dt class="setting__list-label">Registration date:</dt><dd class="setting__list-value">{{ $user->created_at->format('F j, Y') }}</dd></div>
                                    <div class="setting__list-item"><dt class="setting__list-label">Group:</dt><dd class="setting__list-value">{{ $user->is_admin ? 'Administrators' : 'Members' }}</dd></div>
                                    <div class="setting__list-item"><dt class="setting__list-label">Username:</dt><dd class="setting__list-value">{{ $user->username }}</dd></div>
                                </dl>
                            </section>

                            <section class="setting__list">
                                <h2 class="h5">Main</h2>
                                <label class="setting__list-item setting__list-item--center">
                                    <span class="setting__list-label">Username:</span>
                                    <span class="setting__list-value"><input type="text" name="username" value="{{ old('username', $user->username) }}" maxlength="30" required></span>
                                </label>
                                <label class="setting__list-item setting__list-item--center">
                                    <span class="setting__list-label">Full name:</span>
                                    <span class="setting__list-value"><input type="text" name="full_name" value="{{ old('full_name', $user->full_name ?: $user->name) }}" maxlength="255" required></span>
                                </label>
                                <label class="setting__list-item">
                                    <span class="setting__list-label">Signature:</span>
                                    <span class="setting__list-value"><textarea name="signature" rows="3" maxlength="500">{{ old('signature', $user->signature) }}</textarea></span>
                                </label>
                                <label class="setting__list-item">
                                    <span class="setting__list-label">About:</span>
                                    <span class="setting__list-value"><textarea name="bio" maxlength="2000" rows="5">{{ old('bio', $user->bio) }}</textarea></span>
                                </label>
                            </section>

                            <section class="setting__list">
                                <h2 class="h5">Links</h2>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">Website:</span><span class="setting__list-value"><input type="url" name="website" value="{{ old('website', $user->website) }}" placeholder="https://example.com"></span></label>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">Instagram:</span><span class="setting__list-value"><input type="text" name="instagram" value="{{ old('instagram', $user->instagram) }}" placeholder="Username"></span></label>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">Telegram:</span><span class="setting__list-value"><input type="text" name="telegram" value="{{ old('telegram', $user->telegram) }}" placeholder="Username"></span></label>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">Threads:</span><span class="setting__list-value"><input type="text" name="threads" value="{{ old('threads', $user->threads) }}" placeholder="Username"></span></label>
                                <label class="setting__list-item setting__list-item--center"><span class="setting__list-label">YouTube:</span><span class="setting__list-value"><input type="text" name="youtube" value="{{ old('youtube', $user->youtube) }}" placeholder="Username"></span></label>
                                <p class="setting__list-item"><span></span><span class="small color-gray">Enter only the username in social link fields.</span></p>
                            </section>
                            <div class="setting__submit"><button class="btn btn-primary" type="submit">Save</button></div>
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
                        <h1 class="h3">System</h1>
                        <form class="setting__box setting__list" action="{{ route('settings.preferences.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="activeTab" value="site">
                            <label class="setting__list-item setting__list-item--center">
                                <span class="setting__list-label">Time zone</span>
                                <span class="setting__list-value">
                                    <select class="form-select" name="timezone">
                                        @foreach ($timezones as $timezone)
                                            <option value="{{ $timezone }}" @selected(old('timezone', $user->timezone) === $timezone)>{{ $timezone }}</option>
                                        @endforeach
                                    </select>
                                </span>
                            </label>
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="notify_posts" value="1" @checked(old('notify_posts', $user->notify_posts))> <span>Email me about new posts</span></label>
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="notify_replies" value="1" @checked(old('notify_replies', $user->notify_replies))> <span>Notify me about replies</span></label>
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="notify_announcements" value="1" @checked(old('notify_announcements', $user->notify_announcements))> <span>Receive site announcements</span></label>
                            <label class="form-check-label"><input class="form-check-input" type="checkbox" name="show_adult_content" value="1" @checked(old('show_adult_content', $user->show_adult_content))> <span>Show 18+ content</span></label>
                            <div><button class="btn btn-primary" type="submit">Save</button></div>
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
                        <h1 class="h3">Security</h1>
                        <div class="setting__box setting__list">
                            <form class="setting__list" action="{{ route('settings.password.update') }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="activeTab" value="security">
                                <h2 class="h5">Change password</h2>
                                <input type="password" name="current_password" placeholder="Current password" autocomplete="current-password" required>
                                <input type="password" name="password" placeholder="New password" autocomplete="new-password" minlength="12" required>
                                <input type="password" name="password_confirmation" placeholder="Repeat new password" autocomplete="new-password" required>
                                <div><button class="btn btn-primary" type="submit">Update password</button></div>
                            </form>
                            <form class="setting__list" action="{{ route('settings.email.update') }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="activeTab" value="security">
                                <h2 class="h5">Email</h2>
                                <input type="email" name="email" value="{{ $user->email }}" autocomplete="email" required>
                                <input type="password" name="current_password" placeholder="Confirm your password" autocomplete="current-password" required>
                                <div><button class="btn btn-primary" type="submit">Update email</button></div>
                            </form>
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
                        <h1 class="h3">Blacklist</h1>
                        <div class="setting__box">
                            <div class="setting__ignorlist">
                                @forelse ($blockedUsers as $blockedUser)
                                    <span class="ignore-list-item">
                                        @if (filled($blockedUser->username))
                                            <a class="pm_list" href="{{ route('profiles.show', $blockedUser) }}">{{ $blockedUser->full_name ?: $blockedUser->name }}</a>
                                        @else
                                            <span class="pm_list">{{ $blockedUser->full_name ?: $blockedUser->name }}</span>
                                        @endif
                                        <form action="{{ route('settings.blocked-users.destroy', $blockedUser) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="activeTab" value="blacklist">
                                            <button class="btn btn-red-ghost btn-circle btn-sm" type="submit" aria-label="Remove from blacklist"><x-repost-icon name="close" size="14" /></button>
                                        </form>
                                    </span>
                                @empty
                                    <p class="color-gray">Your blacklist is empty.</p>
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
                        <h1 class="h3">Verification</h1>
                        <div class="setting__box">
                            <section class="setting__list">
                                @if ($user->hasVerifiedBadge())
                                    <p>Your account has been successfully verified.</p>
                                    <span class="verify" role="img" aria-label="Verified account"><x-repost-icon name="verify" size="18" /></span>
                                @elseif ($user->verification_status === 'pending')
                                    <p>Your verification request is under review.</p>
                                    <p class="small color-gray">Submitted {{ $user->verification_requested_at?->diffForHumans() }}.</p>
                                    <form action="{{ route('settings.verification.cancel') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="activeTab" value="verify">
                                        <button class="btn btn-secondary" type="submit">Cancel request</button>
                                    </form>
                                @else
                                    @if ($user->verification_status === 'rejected')
                                        <p>Your previous request was not approved. You can submit a new request.</p>
                                    @endif
                                    <p>Request the blue verification badge. Explain how your account can be independently verified. An administrator reviews each request.</p>
                                    <form class="setting__list" action="{{ route('settings.verification.request') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="activeTab" value="verify">
                                        <label for="verification_reason">Verification details</label>
                                        <textarea id="verification_reason" name="verification_reason" rows="5" minlength="20" maxlength="2000" required>{{ old('verification_reason', $user->verification_status === 'rejected' ? $user->verification_reason : '') }}</textarea>
                                        <button class="btn btn-primary" type="submit">Request verification</button>
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
                        <h1 class="h3">Delete account</h1>
                        <form class="setting__box setting__list" action="{{ route('settings.account.destroy') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="activeTab" value="delete">
                            <p>This permanently deletes your account and its comments. This action cannot be undone.</p>
                            <input type="password" name="current_password" placeholder="Confirm your password" autocomplete="current-password" required>
                            <div><button class="btn btn-red" type="submit">Delete account</button></div>
                        </form>
                    </section>
                </div>
            </div>
        </div>

        <aside class="setting__aside">
            <div class="widgets">
                <section class="widget card card-body setting__menu">
                    <div class="widget__content widget__content--bleed">
                        <ul class="menu" aria-label="Settings sections" data-tabs-nav="settings">
                            <li class="menu__profile-item">
                                @if (filled($user->username))
                                    <a href="{{ route('profiles.show', $user) }}" class="menu__profile user-box">
                                @else
                                    <div class="menu__profile user-box">
                                @endif
                                    <span class="avatar"><img src="{{ $user->avatarUrl() }}" alt="" width="40" height="40"></span>
                                    <span class="user-box-content">
                                        <span class="name">
                                            {{ $user->full_name ?: $user->name }}
                                            @if ($user->hasVerifiedBadge())<span class="verify"><x-repost-icon name="verify" size="14" /></span>@endif
                                        </span>
                                        <span class="meta">{{ filled($user->username) ? 'Back to profile' : 'Set a username in Profile settings' }}</span>
                                    </span>
                                @if (filled($user->username))
                                    </a>
                                @else
                                    </div>
                                @endif
                            </li>
                            <li class="menu__divider" aria-hidden="true"></li>
                            @foreach ([
                                'profile' => ['Profile', 'user'],
                                'site' => ['System', 'compass'],
                                'security' => ['Security', 'security'],
                                'blacklist' => ['Blacklist', 'list'],
                                'verify' => ['Verification', 'comment'],
                                'delete' => ['Delete account', 'trash'],
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
            </div>
        </aside>
    </div>
@endsection
