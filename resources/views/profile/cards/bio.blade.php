@php
    use Illuminate\Support\Str;

    $coverPhotoUrl   = $coverPhotoUrl   ?? ($user->cover_photo_url ?? $user->cover_image ?? $user->cover ?? null);
    $profilePhotoUrl = $profilePhotoUrl ?? ($user->profile_photo_url ?? $user->photo ?? $user->avatar ?? null);
    $initials        = $initials        ?? mb_strtoupper(mb_substr($user->name ?? 'K', 0, 2, 'UTF-8'), 'UTF-8');
    $username        = $username        ?? ($user->username ?? $user->slug ?? null);
    $joinedLabel     = $joinedLabel     ?? null;
    $registeredLabel = $registeredLabel ?? optional($user->created_at)->translatedFormat('d M Y');
    $postsCount      = $postsCount      ?? 0;
    $socialLinks     = $socialLinks     ?? [];
    $website         = $website         ?? null;
    $badges          = $badges          ?? [];
    $hasBlockedUser  = $hasBlockedUser  ?? false;
    $isBlockedByUser = $isBlockedByUser ?? false;

    $normalizeSocialUrl = function (?string $value, string $platform): ?string {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $hasScheme = Str::startsWith($value, ['http://', 'https://']);

        switch ($platform) {
            case 'x':
            case 'twitter':
                if (!$hasScheme) {
                    $username = ltrim($value, '@');
                    return "https://x.com/{$username}";
                }
                return $value;
            case 'instagram':
                if (!$hasScheme) {
                    $username = ltrim($value, '@');
                    return "https://instagram.com/{$username}";
                }
                return $value;
            case 'whatsapp':
                if (!$hasScheme) {
                    $digits = preg_replace('/\\D+/', '', $value);
                    return $digits ? "https://wa.me/{$digits}" : null;
                }
                return $value;
            case 'tiktok':
                if (!$hasScheme) {
                    $username = ltrim($value, '@');
                    return "https://www.tiktok.com/@{$username}";
                }
                return $value;
            case 'facebook':
                if (!$hasScheme) {
                    $username = ltrim($value, '@');
                    return "https://facebook.com/{$username}";
                }
                return $value;
            case 'website':
                if ($hasScheme) {
                    return $value;
                }
                return "https://{$value}";
            default:
                return $hasScheme ? $value : null;
        }
    };

    $platformUrls = [
        'x'         => $normalizeSocialUrl($user->social_x ?? $user->x_url ?? $user->x ?? $user->twitter ?? null, 'x'),
        'instagram' => $normalizeSocialUrl($user->social_instagram ?? $user->instagram_url ?? $user->instagram ?? null, 'instagram'),
        'whatsapp'  => $normalizeSocialUrl($user->social_whatsapp ?? $user->whatsapp_url ?? $user->whatsapp ?? null, 'whatsapp'),
        'tiktok'    => $normalizeSocialUrl($user->social_tiktok ?? $user->tiktok_url ?? $user->tiktok ?? null, 'tiktok'),
        'facebook'  => $normalizeSocialUrl($user->social_facebook ?? $user->facebook_url ?? $user->facebook ?? null, 'facebook'),
        'website'   => $normalizeSocialUrl($user->website_url ?? $website ?? $user->website ?? $user->url ?? null, 'website'),
    ];

    $platformIcons = [
        'x' => '<i class="ri-twitter-x-line" aria-hidden="true"></i>',
        'instagram' => '<i class="ri-instagram-line" aria-hidden="true"></i>',
        'whatsapp' => '<i class="ri-whatsapp-line" aria-hidden="true"></i>',
        'tiktok' => '<i class="ri-tiktok-line" aria-hidden="true"></i>',
        'facebook' => '<i class="ri-facebook-circle-line" aria-hidden="true"></i>',
        'website' => '<i class="ri-global-line" aria-hidden="true"></i>',
    ];

    $defaultCover  = 'https://placehold.co/1200x360/0ea5e9/ffffff?text=Kapak+Fotografi';
    $defaultAvatar = 'https://placehold.co/200x200/0ea5e9/ffffff?text=Profil';
    $coverSource   = $coverPhotoUrl ?: $defaultCover;
    $profileSource = $profilePhotoUrl ?: $defaultAvatar;
    $coverHeightClass = $coverHeightClass ?? 'h-44 sm:h-56';

    $joinedMonthYear = optional($user->created_at)->translatedFormat('F Y');
    $joinedText = $joinedMonthYear ? "{$joinedMonthYear}'te katıldı." : null;

    $location = $user->location
        ?? $user->city
        ?? $user->country
        ?? $user->address
        ?? null;

    $workplace = $user->company
        ?? $user->workplace
        ?? $user->job
        ?? $user->occupation
        ?? null;

    $metaLines = collect([
        [
            'value' => $location,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>',
        ],
        [
            'value' => $workplace,
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6"/></svg>',
        ],
    ])->filter(fn ($item) => !empty($item['value']))->values();

    $activeTab = null;
@endphp

{{-- Profil karti --}}
<section class="relative overflow-hidden rounded-3xl">
    <div class="relative overflow-hidden bg-slate-100 {{ $coverHeightClass }}">
        <img src="{{ $coverSource }}" alt="{{ $user->name }} kapak" class="absolute inset-0 h-full w-full object-cover" onerror="this.onerror=null;this.src='{{ $defaultCover }}';">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/45 via-slate-900/10 to-transparent"></div>
    </div>

    <div class="relative px-4 pb-6 pt-14 sm:px-6 sm:pt-16">
        <div class="absolute left-4 top-0 -translate-y-1/2 sm:left-6">
            <div class="relative h-24 w-24 overflow-hidden rounded-2xl bg-slate-100 sm:h-28 sm:w-28">
                <img src="{{ $profileSource }}" alt="{{ $user->name }}" class="h-full w-full object-cover" onerror="this.onerror=null; this.src='{{ $defaultAvatar }}'; this.nextElementSibling.classList.add('hidden');">
                <div class="absolute inset-0 {{ !empty($profilePhotoUrl) ? 'hidden' : 'flex' }} items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 text-lg font-semibold text-slate-600">
                    {{ $initials }}
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">{{ $user->name }}</h1>
                    @if((bool) ($user->is_verified ?? false) || filled($user->verification_badge ?? null) || filled($user->verification_badge_svg ?? null))
                        <span class="relative inline-flex h-6 w-6 items-center justify-center" data-verified-toggle>
                            <x-verification-badge :user="$user" size="lg" />
                            <span data-verified-tooltip class="hidden absolute left-1/2 top-full mt-2 -translate-x-1/2 whitespace-nowrap rounded-md bg-slate-900 px-2 py-1 text-[10px] font-semibold text-white">
                                Dogrulandi
                            </span>
                        </span>
                    @endif
                </div>

                @if(!empty($joinedText))
                    <p class="mt-1 text-sm text-slate-500">{{ $joinedText }}</p>
                @elseif(!empty($registeredLabel))
                    <p class="mt-1 text-sm text-slate-500">{{ $registeredLabel }}</p>
                @endif

                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-700">
                    <span><span class="font-semibold text-slate-900">{{ number_format($postsCount) }}</span> hikayeler</span>
                    <span><span class="font-semibold text-slate-900">{{ number_format($user->followers_count ?? 0) }}</span> takipçi</span>
                    <span><span class="font-semibold text-slate-900">{{ number_format($user->followings_count ?? 0) }}</span> takip etme</span>
                </div>
            </div>

            @if(isset($isOwnProfile) && !$isOwnProfile && !$isBlockedByUser)
                <div class="flex w-full flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end">
                    @if(!$hasBlockedUser)
                        <form method="POST" action="{{ route('users.follow', $user) }}" class="inline-flex">
                            @csrf
                            <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 sm:w-auto">
                                @if(($isFollowing ?? false))
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="none" aria-hidden="true">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19c0-2.21-2.686-4-6-4s-6 1.79-6 4m12-6h6M9 12a4 4 0 1 1 0-8a4 4 0 0 1 0 8Z"/>
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 shrink-0" fill="none" aria-hidden="true">
                                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19c0-2.21-2.686-4-6-4s-6 1.79-6 4m16-3v-3m0 0v-3m0 3h-3m3 0h3M9 12a4 4 0 1 1 0-8a4 4 0 0 1 0 8Z"/>
                                    </svg>
                                @endif
                                {{ ($isFollowing ?? false) ? 'Takibi bırak' : 'Takip etmek' }}
                            </button>
                        </form>
                    @endif

                    @if(($messagesEnabled ?? false))
                        @if(($canStartChat ?? false))
                            <a href="{{ route('messages.show', $user) }}"
                               class="inline-flex w-full items-center justify-center rounded-xl px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 sm:w-auto">
                                Mesajlas
                            </a>
                        @elseif(($allowFollowingOnly ?? true))
                            <button type="button" disabled
                                class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-400 sm:w-auto">
                                Mesaj icin takip et
                            </button>
                        @endif
                    @endif

                    <details class="relative" data-profile-actions>
                        <summary
                            class="list-none inline-flex h-11 w-full items-center justify-center rounded-xl text-slate-700 transition hover:bg-slate-50 focus:outline-none cursor-pointer marker:hidden sm:w-11">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/>
                            </svg>
                        </summary>

                        <div
                            class="absolute right-0 top-full z-30 mt-2 w-56 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl">
                            <div class="py-1 text-sm text-slate-700">
                                <button type="button"
                                    data-report-open
                                    class="flex w-full items-center gap-2 px-4 py-2 text-left hover:bg-slate-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M10.29 3.86 2.82 18a1.5 1.5 0 0 0 1.31 2.2h15.74a1.5 1.5 0 0 0 1.31-2.2L13.71 3.86a1.5 1.5 0 0 0-2.62 0Z"/>
                                    </svg>
                                    Sikayet et
                                </button>

                                <form method="POST" action="{{ route('users.block', $user) }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-left hover:bg-slate-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5 19 19M5 19 19 5"/>
                                        </svg>
                                        {{ $hasBlockedUser ? 'Engeli kaldir' : 'Engelle' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </details>
                </div>
            @endif
        </div>

        <p class="mt-4 text-sm leading-6 text-slate-700">
            {{ $user->bio ?: 'Kisa bir biyografi ekleyerek toplulugun seni tanimasina yardimci ol.' }}
        </p>

        @if($metaLines->isNotEmpty())
            <div class="mt-4 space-y-2 text-sm text-slate-700">
                @foreach($metaLines as $line)
                    <div class="flex items-start gap-2">
                        <span class="mt-0.5 text-slate-500">{!! $line['icon'] !!}</span>
                        <span class="leading-6">{{ $line['value'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        @php
            $socialList = collect($socialLinks)->filter(function ($item) {
                $url = is_array($item) ? ($item['url'] ?? $item['link'] ?? null) : null;
                return !empty($url);
            });

            foreach ($platformUrls as $key => $url) {
                if (empty($url)) continue;

                $alreadyAdded = $socialList->first(function ($item) use ($url, $key) {
                    $title = is_array($item) ? strtolower($item['title'] ?? $item['name'] ?? '') : '';
                    $link  = is_array($item) ? ($item['url'] ?? $item['link'] ?? null) : null;
                    return $link === $url || $title === strtolower($key);
                });

                if (!$alreadyAdded) {
                    $socialList->push([
                        'title' => ucfirst($key),
                        'url'   => $url,
                        'icon'  => $platformIcons[$key] ?? null,
                    ]);
                }
            }
        @endphp

        @if($socialList->isNotEmpty())
            <div class="mt-4 flex flex-wrap items-center gap-0">
                @foreach($socialList as $link)
                    @php
                        $linkTitle = $link['title'] ?? $link['name'] ?? 'Sosyal baglanti';
                        $linkUrl   = $link['url'] ?? $link['link'] ?? null;
                        $fallback  = mb_strtoupper(mb_substr($linkTitle, 0, 1, 'UTF-8'), 'UTF-8');
                        $titleLower = Str::lower((string) $linkTitle);

                        $platformKey = null;
                        if (Str::contains($titleLower, ['instagram'])) {
                            $platformKey = 'instagram';
                        } elseif (Str::contains($titleLower, ['whatsapp', 'wa.me'])) {
                            $platformKey = 'whatsapp';
                        } elseif (Str::contains($titleLower, ['tiktok'])) {
                            $platformKey = 'tiktok';
                        } elseif (Str::contains($titleLower, ['facebook', 'fb'])) {
                            $platformKey = 'facebook';
                        } elseif (Str::contains($titleLower, ['twitter', ' x', 'x.com', '@x', 'x ']) || $titleLower === 'x') {
                            $platformKey = 'x';
                        } elseif (Str::contains($titleLower, ['web', 'site', 'http'])) {
                            $platformKey = 'website';
                        }

                        $platformColor = match ($platformKey) {
                            'instagram' => 'text-pink-600 hover:text-pink-700',
                            'whatsapp' => 'text-emerald-600 hover:text-emerald-700',
                            'tiktok' => 'text-slate-900 hover:text-slate-900',
                            'facebook' => 'text-gray-600 hover:text-gray-700',
                            'x' => 'text-slate-900 hover:text-slate-900',
                            default => 'text-slate-700 hover:text-slate-900',
                        };

                        $iconHtml = $platformKey ? ($platformIcons[$platformKey] ?? null) : null;
                    @endphp
                    <a href="{{ $linkUrl }}" target="_blank" rel="noopener"
                       class="inline-flex h-8 w-8 items-center justify-center rounded-full transition hover:-translate-y-[1px] hover:bg-slate-50 no-underline {{ $platformColor }}"
                       aria-label="{{ $linkTitle }}">
                        @if(!empty($iconHtml))
                            <span class="text-[16px] leading-none text-current">
                                {!! $iconHtml !!}
                            </span>
                        @else
                            <span class="text-xs font-semibold">{{ $fallback }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif

        @if(!empty($badges))
            <div class="mt-4 flex flex-wrap items-center gap-2">
                @foreach($badges as $badge)
                    @php
                        $badgeLabel = is_array($badge) ? ($badge['label'] ?? $badge['name'] ?? $badge['title'] ?? 'Rozet') : $badge;
                        $badgeIcon  = is_array($badge) ? ($badge['icon'] ?? $badge['svg'] ?? null) : null;
                        $badgeFallback = mb_strtoupper(mb_substr((string) $badgeLabel, 0, 1, 'UTF-8'), 'UTF-8');
                    @endphp
                    @if(!empty($badgeIcon))
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-700">
                            {!! $badgeIcon !!}
                        </span>
                    @else
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-700">
                            <span class="text-sm font-bold">{{ $badgeFallback }}</span>
                        </span>
                    @endif
                @endforeach
            </div>
        @endif


    </div>
</section>

{{-- Sikayet modal --}}
<div id="reportModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 p-4" aria-hidden="true">
    <div class="flex w-full max-w-xl max-h-[90vh] flex-col overflow-hidden rounded-2xl">
        <div class="flex items-start gap-3 px-4 py-3 sm:px-5 sm:py-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 2.82 18a1.5 1.5 0 0 0 1.31 2.2h15.74a1.5 1.5 0 0 0 1.31-2.2L13.71 3.86a1.5 1.5 0 0 0-2.62 0Z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Guvenlik</p>
                <h3 class="text-lg font-bold text-slate-900">Bildirme vakti geldi</h3>
                <p class="text-sm text-slate-600">Sikayet edecegin kisi: <span class="font-semibold text-slate-900">{{ $user->name }}</span></p>
            </div>
            <button type="button" data-report-close class="-m-2 rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none">x</button>
        </div>

        <form class="flex-1 space-y-4 overflow-y-auto px-4 py-4 sm:px-5" action="{{ route('users.report', $user) }}" method="POST">
            @csrf
            <div class="flex flex-col gap-3 rounded-xl bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-900">Kullanici adim gorunsun mu?</p>
                    <p class="text-xs text-slate-500">Sikayetin kullanici adinla paylasilir.</p>
                </div>
                <div class="shrink-0">
                    <input type="hidden" name="show_username" value="0">
                    <x-ui.switch
                        name="show_username"
                        value="1"
                        :checked="old('show_username', 1) == 1"
                    />
                </div>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-900 mb-2">Konu</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach(['Taciz','Zorbalik','Dolandirici','Kimlik taklidi','Casus veya supheli','Satici','Istenmeyen','Olasi aktivite'] as $topic)
                        <label class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-gray-100">
                            <input type="radio" name="topic" value="{{ $topic }}" class="h-4 w-4 text-gray-500" required>
                            <span>{{ $topic }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="reportMessage" class="text-sm font-semibold text-slate-900">Aciklama</label>
                <textarea id="reportMessage" name="message" rows="3" class="mt-2 w-full rounded-xl px-3 py-2 text-sm text-slate-700 focus:outline-none" placeholder="Kisa bir aciklama ekle..."></textarea>
            </div>

            <label class="flex items-start gap-3 text-sm text-slate-700">
                <input type="checkbox" name="terms" class="mt-1 h-4 w-4 rounded text-gray-500" required>
                <span>Hukum ve sartlari kabul ediyorum.</span>
            </label>

            <div class="flex flex-col-reverse items-stretch gap-2 pt-2 sm:flex-row sm:items-center sm:justify-end sm:gap-3">
                <button type="button" data-report-close class="rounded-full px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-100">Vazgec</button>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    Gonder
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const profileActions = Array.from(document.querySelectorAll('details[data-profile-actions]'));
        const closeProfileActions = () => profileActions.forEach((el) => el.removeAttribute('open'));

        document.addEventListener('click', (e) => {
            profileActions.forEach((el) => {
                if (!el.contains(e.target)) el.removeAttribute('open');
            });
        });

        document.querySelectorAll('[data-verified-toggle]').forEach((el) => {
            const tip = el.querySelector('[data-verified-tooltip]');
            if (!tip) return;

            let clickCount = 0;
            let clickTimer = null;

            el.addEventListener('mouseenter', () => tip.classList.remove('hidden'));
            el.addEventListener('mouseleave', () => tip.classList.add('hidden'));

            el.addEventListener('click', () => {
                clickCount += 1;
                if (clickTimer) clearTimeout(clickTimer);

                if (clickCount >= 2) {
                    tip.classList.remove('hidden');
                    setTimeout(() => tip.classList.add('hidden'), 1200);
                    clickCount = 0;
                    return;
                }

                clickTimer = setTimeout(() => {
                    clickCount = 0;
                }, 400);
            });
        });

        const modal = document.getElementById('reportModal');
        if (!modal) return;
        const openers = document.querySelectorAll('[data-report-open]');
        const closers = modal.querySelectorAll('[data-report-close]');

        const show = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.documentElement.style.overflow = 'hidden';
        };

        const hide = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
            document.documentElement.style.overflow = '';
        };

        openers.forEach(btn => btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeProfileActions();
            show();
        }));

        closers.forEach(btn => btn.addEventListener('click', hide));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) hide();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            closeProfileActions();
            if (!modal.classList.contains('hidden')) hide();
        });
    });
</script>




