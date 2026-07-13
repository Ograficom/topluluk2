@php
    $homeActive = request()->routeIs('home');
    $searchActive = request()->routeIs('search');
    $videoActive = request()->routeIs('video');
    $messagesActive = request()->routeIs('messages.*');
    $mobileNavI18n = [
        'sectionPosts' => __('site.mobile_nav.section_posts'),
        'sectionTags' => __('site.mobile_nav.section_tags'),
        'sectionCategories' => __('site.mobile_nav.section_categories'),
        'sectionUsers' => __('site.mobile_nav.section_users'),
        'sectionPages' => 'Sayfalar',
        'noResults' => __('site.mobile_nav.no_results'),
        'emptyQuery' => __('site.mobile_nav.empty_query'),
        'searchDisabled' => __('site.mobile_nav.search_disabled'),
        'searchFailed' => __('site.mobile_nav.search_failed'),
        'searchTooShort' => __('site.mobile_nav.search_too_short'),
    ];
@endphp

<nav data-mobile-bottom-nav class="fixed bottom-3 left-1/2 z-50 h-[64px] w-[calc(100%_-_12px)] max-w-[390px] -translate-x-1/2 rounded-[22px] border border-black/10 bg-white px-2 sm:hidden" aria-label="{{ __('site.mobile_nav.menu') }}">
    <div class="grid h-full grid-cols-5 items-center gap-1">
        <a
            href="{{ route('home') }}"
            class="inline-flex h-11 items-center justify-center rounded-[14px] bg-transparent transition hover:bg-transparent {{ $homeActive ? 'text-emerald-600' : 'text-slate-900' }}"
            aria-label="{{ __('site.mobile_nav.home') }}"
        >
            <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="m4.75 10.75 7.25-6 7.25 6V19a1.25 1.25 0 0 1-1.25 1.25h-3.25v-4.5a1.25 1.25 0 0 0-1.25-1.25h-3a1.25 1.25 0 0 0-1.25 1.25v4.5H6A1.25 1.25 0 0 1 4.75 19v-8.25Z" />
            </svg>
            <span class="sr-only">{{ __('site.mobile_nav.home') }}</span>
        </a>

        <button
            type="button"
            data-mobile-search-toggle
            class="inline-flex h-11 items-center justify-center rounded-[14px] bg-transparent transition hover:bg-transparent {{ $searchActive ? 'text-emerald-600' : 'text-slate-900' }}"
            aria-label="{{ __('site.mobile_nav.search') }}"
        >
            <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="6.75" stroke="currentColor" stroke-width="1.9"></circle>
                <path stroke="currentColor" stroke-linecap="round" stroke-width="1.9" d="m16 16 3.75 3.75"></path>
            </svg>
            <span class="sr-only">{{ __('site.mobile_nav.search') }}</span>
        </button>

        <div class="flex items-center justify-center">
            @auth
                <a
                    href="{{ route('blog.create') }}"
                    class="mobile-bottom-nav__plus inline-flex h-[46px] w-[46px] items-center justify-center rounded-[16px] border border-transparent bg-transparent text-slate-900 ring-0 transition hover:-translate-y-0.5"
                    aria-label="{{ __('site.mobile_nav.new_item') }}"
                >
            @else
                <a
                    href="{{ route('login') }}"
                    class="mobile-bottom-nav__plus inline-flex h-[46px] w-[46px] items-center justify-center rounded-[16px] border border-transparent bg-transparent text-slate-900 ring-0 transition hover:-translate-y-0.5"
                    aria-label="{{ __('site.mobile_nav.new_item') }}"
                >
            @endauth
                    <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2.2" d="M12 5v14M5 12h14" />
                    </svg>
                    <span class="sr-only">{{ __('site.mobile_nav.new_item') }}</span>
                </a>
        </div>

        <a
            href="{{ route('video') }}"
            class="inline-flex h-11 items-center justify-center rounded-[14px] bg-transparent transition hover:bg-transparent {{ $videoActive ? 'text-emerald-600' : 'text-slate-900' }}"
            aria-label="Video"
        >
            <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M6.75 5.75h7.5A3.25 3.25 0 0 1 17.5 9v6a3.25 3.25 0 0 1-3.25 3.25h-7.5A3.25 3.25 0 0 1 3.5 15V9a3.25 3.25 0 0 1 3.25-3.25Z" />
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="m17.5 10 3-2v8l-3-2" />
            </svg>
            <span class="sr-only">Video</span>
        </a>

        @auth
            <a
                href="{{ route('messages.index') }}"
                class="inline-flex h-11 items-center justify-center rounded-[14px] bg-transparent transition hover:bg-transparent {{ $messagesActive ? 'text-emerald-600' : 'text-slate-900' }}"
                aria-label="{{ __('site.sidebar.messages') }}"
            >
                <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M7 6.75h10A3.25 3.25 0 0 1 20.25 10v4A3.25 3.25 0 0 1 17 17.25h-5.72L7 20.75v-3.5A3.25 3.25 0 0 1 3.75 14v-4A3.25 3.25 0 0 1 7 6.75Z" />
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="1.9" d="M8.75 11.5h6.5M8.75 14h4.5" />
                </svg>
                <span class="sr-only">{{ __('site.sidebar.messages') }}</span>
            </a>
        @else
            <button
                type="button"
                data-mobile-login-toggle
                class="inline-flex h-11 items-center justify-center rounded-[14px] bg-transparent text-slate-900 transition hover:bg-transparent"
                aria-label="{{ __('site.sidebar.messages') }}"
            >
                <svg class="h-6 w-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M7 6.75h10A3.25 3.25 0 0 1 20.25 10v4A3.25 3.25 0 0 1 17 17.25h-5.72L7 20.75v-3.5A3.25 3.25 0 0 1 3.75 14v-4A3.25 3.25 0 0 1 7 6.75Z" />
                    <path stroke="currentColor" stroke-linecap="round" stroke-width="1.9" d="M8.75 11.5h6.5M8.75 14h4.5" />
                </svg>
                <span class="sr-only">{{ __('site.sidebar.messages') }}</span>
            </button>
        @endauth
    </div>
</nav>

<div data-mobile-login-drawer class="pointer-events-none fixed inset-0 z-50 flex flex-col items-center justify-end sm:hidden">
    <div data-mobile-login-backdrop class="absolute inset-0 bg-black/40 opacity-0 transition-opacity duration-200"></div>
    <aside class="relative w-full max-w-md translate-y-full rounded-t-3xl bg-white p-6 transition-transform duration-200">
        <div class="mb-3 flex justify-center">
            <span data-mobile-login-handle class="h-1.5 w-12 rounded-full bg-slate-200"></span>
        </div>
        <div class="mb-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true">
                    <path d="M368 16H144a64.07 64.07 0 0 0-64 64v352a64.07 64.07 0 0 0 64 64h224a64.07 64.07 0 0 0 64-64V80a64.07 64.07 0 0 0-64-64Zm-34.52 268.51c7.57 8.17 11.27 19.16 10.39 30.94C342.14 338.91 324.25 358 304 358s-38.17-19.09-39.88-42.55c-.86-11.9 2.81-22.91 10.34-31S292.4 272 304 272a39.65 39.65 0 0 1 29.48 12.51ZM192 80a16 16 0 0 1 16-16h96a16 16 0 0 1 0 32h-96a16 16 0 0 1-16-16Zm189 363.83a12.05 12.05 0 0 1-9.31 4.17H236.31a12.05 12.05 0 0 1-9.31-4.17a13 13 0 0 1-2.76-10.92c3.25-17.56 13.38-32.31 29.3-42.66C267.68 381.06 285.6 376 304 376s36.32 5.06 50.46 14.25c15.92 10.35 26.05 25.1 29.3 42.66a13 13 0 0 1-2.76 10.92Z"/>
                </svg>
                <p class="text-lg font-semibold text-slate-900">{{ __('site.mobile_nav.login_title') }}</p>
            </div>
        </div>
        <div class="space-y-3">
            <a class="flex w-full items-center justify-center gap-2 rounded-2xl py-3 text-sm font-semibold text-slate-700" href="{{ route('social.redirect', 'google') }}">
                <svg viewBox="0 0 16 16" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <g fill="none" fill-rule="evenodd" clip-rule="evenodd">
                        <path fill="#F44336" d="M7.209 1.061c.725-.081 1.154-.081 1.933 0a6.57 6.57 0 0 1 3.65 1.82a100 100 0 0 0-1.986 1.93q-1.876-1.59-4.188-.734q-1.696.78-2.362 2.528a78 78 0 0 1-2.148-1.658a.26.26 0 0 0-.16-.027q1.683-3.245 5.26-3.86"/>
                        <path fill="#FFC107" d="M1.946 4.92q.085-.013.161.027a78 78 0 0 0 2.148 1.658A7.6 7.6 0 0 0 4.04 7.99q.037.678.215 1.331L2 11.116Q.527 8.038 1.946 4.92"/>
                        <path fill="#448AFF" d="M12.685 13.29a26 26 0 0 0-2.202-1.74q1.15-.812 1.396-2.228H8.122V6.713q3.25-.027 6.497.055q.616 3.345-1.423 6.032a7 7 0 0 1-.51.49"/>
                        <path fill="#43A047" d="M4.255 9.322q1.23 3.057 4.51 2.854a3.94 3.94 0 0 0 1.718-.626q1.148.812 2.202 1.74a6.62 6.62 0 0 1-4.027 1.684a6.4 6.4 0 0 1-1.02 0Q3.82 14.524 2 11.116z"/>
                    </g>
                </svg>
                {{ __('site.mobile_nav.login_with_google') }}
            </a>
            <div class="text-center text-xs uppercase tracking-[0.3em] text-slate-500">{{ __('site.common.or') }}</div>
            <form method="POST" action="{{ route('login') }}" class="space-y-3">
                @csrf
                <label for="mobile-login-email" class="block text-xs font-semibold text-slate-600">{{ __('site.mobile_nav.email') }}</label>
                <input id="mobile-login-email" name="email" type="email" required autocomplete="email" class="w-full rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-800 focus:outline-none" placeholder="{{ __('site.mobile_nav.email') }}">
                <label for="mobile-login-password" class="block text-xs font-semibold text-slate-600">{{ __('site.mobile_nav.password') }}</label>
                <div class="relative">
                    <input id="mobile-login-password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-2xl bg-slate-50 px-4 py-3 pr-12 text-sm text-slate-800 focus:outline-none" placeholder="{{ __('site.mobile_nav.password') }}">
                    <button type="button" data-mobile-password-toggle class="absolute inset-y-0 end-2 flex h-10 w-10 items-center justify-center text-slate-500 hover:text-slate-700" aria-label="{{ __('site.mobile_nav.password_visibility') }}">
                        <svg data-eye-open xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor" stroke="none" aria-hidden="true">
                            <path d="M12 9.005a4 4 0 1 1 0 8a4 4 0 0 1 0-8Zm0 1.5a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5ZM12 5.5c4.613 0 8.596 3.15 9.701 7.564a.75.75 0 1 1-1.455.365a8.504 8.504 0 0 0-16.493.004a.75.75 0 0 1-1.456-.363A10.003 10.003 0 0 1 12 5.5Z"/>
                        </svg>
                        <svg data-eye-closed class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true">
                            <path d="M12 17.5c-3.8 0-7.2-2.1-8.8-5.5H1c1.7 4.4 6 7.5 11 7.5s9.3-3.1 11-7.5h-2.2c-1.6 3.4-5 5.5-8.8 5.5"/>
                        </svg>
                    </button>
                </div>
                <div class="flex items-center justify-between text-sm text-slate-500">
                    <label class="inline-flex items-center gap-2">
                        <input id="mobile-login-remember" type="checkbox" name="remember" class="h-4 w-4 rounded text-slate-900">
                        {{ __('site.mobile_nav.stay_signed_in') }}
                    </label>
                    <a href="{{ route('password.request') }}" class="text-xs text-slate-500 hover:text-slate-700">{{ __('site.mobile_nav.forgot_password') }}</a>
                </div>
                <button type="submit" class="w-full rounded-2xl bg-slate-800 py-3 text-sm font-semibold text-white">{{ __('site.mobile_nav.login_button') }}</button>
                <p class="text-center text-xs text-slate-500">
                    {{ __('site.mobile_nav.no_account') }}
                    <a class="font-semibold text-slate-800" href="{{ route('register') }}">{{ __('site.mobile_nav.become_member') }}</a>
                </p>
            </form>
        </div>
    </aside>
</div>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        const i18n = @json($mobileNavI18n);
        const drawer = document.querySelector('[data-mobile-login-drawer]');
        const backdrop = document.querySelector('[data-mobile-login-backdrop]');
        const openers = document.querySelectorAll('[data-mobile-login-toggle]');
        const handle = drawer?.querySelector('[data-mobile-login-handle]');
        const drawerPanel = drawer?.querySelector('aside');
        const body = document.body;
        const passwordToggle = drawerPanel?.querySelector('[data-mobile-password-toggle]');
        const passwordInput = drawerPanel?.querySelector('input[name="password"]');

        if (!drawer || !drawerPanel) return;

        let dragging = false;
        let startY = 0;
        let currentY = 0;

        const open = () => {
            drawer.classList.remove('pointer-events-none');
            drawerPanel.classList.remove('translate-y-full');
            drawerPanel.style.transition = 'transform 0.2s ease';
            drawerPanel.style.transform = 'translateY(0)';
            backdrop?.classList.add('opacity-100');
            body.classList.add('overflow-hidden');
        };

        const close = () => {
            drawerPanel.classList.add('translate-y-full');
            drawerPanel.style.transition = 'transform 0.2s ease';
            drawerPanel.style.transform = 'translateY(100%)';
            backdrop?.classList.remove('opacity-100');
            drawer.classList.add('pointer-events-none');
            body.classList.remove('overflow-hidden');
            setTimeout(() => {
                drawerPanel.style.transform = '';
                drawerPanel.style.transition = '';
            }, 220);
        };

        const handleDragStart = (event) => {
            dragging = true;
            startY = event.touches ? event.touches[0].clientY : event.clientY;
            currentY = 0;
            drawerPanel.style.transition = 'none';
            event.preventDefault();
        };

        const handleDragMove = (event) => {
            if (!dragging) return;
            const clientY = event.touches ? event.touches[0].clientY : event.clientY;
            currentY = Math.max(0, clientY - startY);
            drawerPanel.style.transform = `translateY(${currentY}px)`;
            event.preventDefault();
        };

        const handleDragEnd = () => {
            if (!dragging) return;
            dragging = false;
            drawerPanel.style.transition = 'transform 0.2s ease';
            if (currentY > 80) {
                close();
            } else {
                drawerPanel.style.transform = 'translateY(0)';
            }
            currentY = 0;
        };

        openers.forEach((el) => el.addEventListener('click', open));
        backdrop?.addEventListener('click', close);
        handle?.addEventListener('pointerdown', handleDragStart);
        handle?.addEventListener('touchstart', handleDragStart);
        document.addEventListener('pointermove', handleDragMove);
        document.addEventListener('touchmove', handleDragMove);
        document.addEventListener('pointerup', handleDragEnd);
        document.addEventListener('touchend', handleDragEnd);

        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                passwordToggle.querySelector('[data-eye-open]')?.classList.toggle('hidden', !isPassword);
                passwordToggle.querySelector('[data-eye-closed]')?.classList.toggle('hidden', isPassword);
            });
        }

        const profileToggle = document.querySelector('[data-profile-menu-toggle]');
        const profileMenu = document.querySelector('[data-profile-menu-panel]');
        if (profileToggle && profileMenu) {
            const hideProfileMenu = () => {
                profileMenu.classList.add('hidden');
                profileToggle.setAttribute('aria-expanded', 'false');
            };

            profileToggle.addEventListener('click', (event) => {
                event.stopPropagation();
                profileMenu.classList.toggle('hidden');
                profileToggle.setAttribute('aria-expanded', profileMenu.classList.contains('hidden') ? 'false' : 'true');
            });

            document.addEventListener('click', (event) => {
                if (profileMenu.contains(event.target) || profileToggle.contains(event.target)) {
                    return;
                }
                hideProfileMenu();
            });

            document.addEventListener('touchstart', (event) => {
                if (profileMenu.contains(event.target) || profileToggle.contains(event.target)) {
                    return;
                }
                hideProfileMenu();
            });
        }

        const searchToggle = document.querySelector('[data-mobile-search-toggle]');
        const searchDrawer = document.querySelector('[data-mobile-search-drawer]');
        const searchBackdrop = document.querySelector('[data-mobile-search-backdrop]');
        const searchSurface = searchDrawer?.querySelector('[data-mobile-search-surface]');
        const searchInput = searchDrawer?.querySelector('[data-mobile-search-input]');
        const searchClear = searchDrawer?.querySelector('[data-mobile-search-clear]');
        const searchCloseButtons = searchDrawer?.querySelectorAll('[data-mobile-search-close]');
        const searchResultsWrap = searchDrawer?.querySelector('[data-mobile-search-results]');
        const searchAllBtn = searchDrawer?.querySelector('[data-mobile-search-all]');
        const searchAllLabel = searchDrawer?.querySelector('[data-mobile-search-all-label]');
        const searchPageUrl = @json(route('search'));
        let searchAbortController = null;
        let searchDebounceTimer = null;
        let searchHideTimer = null;

        const escapeHtml = (value = '') => String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');

        const syncSearchClear = () => {
            if (!searchClear) return;
            searchClear.classList.toggle('hidden', !searchInput?.value.trim());
        };

        const setSearchAllState = (query = '', visible = false) => {
            if (!searchAllBtn || !searchAllLabel) return;
            const clean = query.trim();
            searchAllBtn.classList.toggle('hidden', !visible || !clean);
            searchAllLabel.textContent = clean ? `Tum sonuclari gor: ${clean}` : 'Tum sonuclari gor';
        };

        const renderMessage = (message, query = '') => {
            if (!searchResultsWrap) return;
            searchResultsWrap.innerHTML = `<p class="px-4 py-5 text-sm text-slate-500">${escapeHtml(message)}</p>`;
            setSearchAllState(query, false);
        };

        const searchIconSvg = `
            <svg class="h-5 w-5 text-slate-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="6.75" stroke="currentColor" stroke-width="1.9"></circle>
                <path stroke="currentColor" stroke-linecap="round" stroke-width="1.9" d="m16 16 3.75 3.75"></path>
            </svg>
        `;

        const sectionWrapper = (label, itemsHtml) => `
            <section class="px-2 py-2">
                <div class="mb-2 flex items-center gap-2 px-2 text-[0.78rem] font-semibold text-slate-500">
                    <span>${escapeHtml(label)}</span>
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>
                <div class="space-y-1">${itemsHtml}</div>
            </section>
        `;

        const buildRow = (innerHtml, url = '#') => `
            <a href="${escapeHtml(url)}" class="flex items-center gap-3 rounded-[16px] px-3 py-2.5 text-sm text-slate-900 transition hover:bg-slate-100">
                ${innerHtml}
            </a>
        `;

        const renderSearchPayload = (payload = {}) => {
            if (!searchResultsWrap) return;

            const posts = Array.isArray(payload.posts) ? payload.posts : [];
            const tags = Array.isArray(payload.tags) ? payload.tags : [];
            const categories = Array.isArray(payload.categories) ? payload.categories : [];
            const users = Array.isArray(payload.users) ? payload.users : [];
            const pages = Array.isArray(payload.pages) ? payload.pages : [];
            const sections = [];

            if (users.length) {
                const html = users.map((user) => {
                    const title = escapeHtml(user.title ?? '');
                    const avatar = user.avatar
                        ? `<img src="${escapeHtml(user.avatar)}" alt="${title}" class="h-9 w-9 rounded-full object-cover" />`
                        : `<span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-700">${escapeHtml((user.title || 'U').trim().charAt(0).toUpperCase() || 'U')}</span>`;

                    return buildRow(`
                        ${avatar}
                        <span class="min-w-0 truncate text-[1.03rem] font-semibold">${title}</span>
                    `, user.url ?? '#');
                }).join('');

                sections.push(sectionWrapper(i18n.sectionUsers, html));
            }

            if (posts.length) {
                const html = posts.map((post) => buildRow(`
                    ${searchIconSvg}
                    <span class="min-w-0 truncate text-[1.03rem] font-semibold">${escapeHtml(post.title ?? '')}</span>
                `, post.url ?? '#')).join('');

                sections.push(sectionWrapper(i18n.sectionPosts, html));
            }

            if (categories.length) {
                const html = categories.map((category) => buildRow(`
                    ${searchIconSvg}
                    <span class="min-w-0 truncate text-[1.03rem] font-semibold">${escapeHtml(category.title ?? '')}</span>
                `, category.url ?? '#')).join('');

                sections.push(sectionWrapper(i18n.sectionCategories, html));
            }

            if (tags.length) {
                const html = tags.map((tag) => buildRow(`
                    ${searchIconSvg}
                    <span class="min-w-0 truncate text-[1.03rem] font-semibold">#${escapeHtml(tag.title ?? '')}</span>
                `, tag.url ?? '#')).join('');

                sections.push(sectionWrapper(i18n.sectionTags, html));
            }

            if (pages.length) {
                const html = pages.map((page) => buildRow(`
                    ${searchIconSvg}
                    <span class="min-w-0 truncate text-[1.03rem] font-semibold">${escapeHtml(page.title ?? '')}</span>
                `, page.url ?? '#')).join('');

                sections.push(sectionWrapper(i18n.sectionPages, html));
            }

            const total = posts.length + tags.length + categories.length + users.length + pages.length;
            if (total === 0) {
                renderMessage(i18n.noResults, searchInput?.value ?? '');
                return;
            }

            searchResultsWrap.innerHTML = sections.join('');
            setSearchAllState(searchInput?.value ?? '', true);
        };

        const fetchLiveSearch = async (query) => {
            if (!query) {
                renderMessage(i18n.emptyQuery);
                return;
            }

            if (searchAbortController) {
                searchAbortController.abort();
            }
            searchAbortController = new AbortController();

            try {
                const response = await fetch(`${searchPageUrl}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' },
                    signal: searchAbortController.signal,
                });

                if (!response.ok) {
                    throw new Error('Network error');
                }

                const json = await response.json();
                const { data, meta } = json;

                if (meta && meta.too_short) {
                    renderMessage(i18n.searchTooShort.replace(':min', String(meta.min_length ?? 0)), query);
                    return;
                }

                if (meta && !meta.enabled) {
                    renderMessage(i18n.searchDisabled, query);
                    return;
                }

                renderSearchPayload(data ?? {});
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }
                renderMessage(i18n.searchFailed, query);
            }
        };

        const handleSearchInput = () => {
            if (!searchInput) return;
            clearTimeout(searchDebounceTimer);
            syncSearchClear();
            const query = searchInput.value.trim();
            if (!query) {
                renderMessage(i18n.emptyQuery);
                return;
            }
            searchDebounceTimer = setTimeout(() => fetchLiveSearch(query), 180);
        };

        const openSearch = () => {
            if (!searchDrawer || !searchSurface) return;
            if (searchHideTimer) {
                clearTimeout(searchHideTimer);
                searchHideTimer = null;
            }
            searchDrawer.classList.remove('pointer-events-none');
            requestAnimationFrame(() => {
                searchBackdrop?.classList.remove('opacity-0');
                searchSurface.classList.remove('translate-y-3', 'scale-[0.98]', 'opacity-0');
            });
            document.querySelector('[data-mobile-bottom-nav]')?.classList.add('pointer-events-none', 'opacity-0');
            body.classList.add('overflow-hidden');
            syncSearchClear();
            handleSearchInput();
            searchInput?.focus();
        };

        const closeSearch = () => {
            if (!searchDrawer || !searchSurface) return;
            searchBackdrop?.classList.add('opacity-0');
            searchSurface.classList.add('translate-y-3', 'scale-[0.98]', 'opacity-0');
            document.querySelector('[data-mobile-bottom-nav]')?.classList.remove('pointer-events-none', 'opacity-0');
            body.classList.remove('overflow-hidden');
            clearTimeout(searchDebounceTimer);
            searchAbortController?.abort();
            searchAbortController = null;
            searchHideTimer = window.setTimeout(() => {
                searchDrawer.classList.add('pointer-events-none');
            }, 220);
        };

        const mobileNavigateSearch = (query) => {
            const value = (query || '').trim();
            if (!value) return;
            window.location.href = `${searchPageUrl}?q=${encodeURIComponent(value)}`;
        };

        searchToggle?.addEventListener('click', () => {
            if (searchDrawer?.classList.contains('pointer-events-none')) {
                openSearch();
                return;
            }

            closeSearch();
        });
        searchBackdrop?.addEventListener('click', closeSearch);
        searchCloseButtons?.forEach((button) => button.addEventListener('click', closeSearch));
        searchClear?.addEventListener('click', (event) => {
            event.preventDefault();
            if (!searchInput) return;
            searchInput.value = '';
            syncSearchClear();
            renderMessage(i18n.emptyQuery);
            searchInput.focus();
        });
        searchInput?.addEventListener('input', handleSearchInput);
        searchInput?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                mobileNavigateSearch(searchInput.value);
                closeSearch();
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                closeSearch();
            }
        });
        searchAllBtn?.addEventListener('click', (event) => {
            event.preventDefault();
            mobileNavigateSearch(searchInput?.value);
            closeSearch();
        });
    });
</script>

<div data-mobile-search-drawer class="pointer-events-none fixed inset-0 z-[60] sm:hidden">
    <div data-mobile-search-backdrop class="absolute inset-0 bg-[#f7f8fa] opacity-0 transition-opacity duration-200"></div>
    <div data-mobile-search-surface class="relative mx-auto flex h-full w-full max-w-[430px] translate-y-3 scale-[0.98] flex-col bg-[#f7f8fa] px-3 pb-4 pt-3 opacity-0 transition duration-200 ease-out">
        <div class="flex items-center gap-2">
            <form action="{{ route('search') }}" method="GET" class="flex-1">
                <label class="flex h-[42px] items-center gap-3 rounded-[18px] border border-slate-200 bg-white px-4 text-slate-900 shadow-[0_12px_24px_-18px_rgba(15,23,42,0.16)] transition hover:bg-slate-100">
                    <svg class="h-5 w-5 shrink-0 text-slate-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="6.75" stroke="currentColor" stroke-width="1.9"></circle>
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="1.9" d="m16 16 3.75 3.75"></path>
                    </svg>
                    <input
                        name="q"
                        type="search"
                        class="min-w-0 flex-1 bg-transparent text-[15px] text-slate-900 outline-none placeholder:text-slate-400"
                        placeholder="{{ __('site.mobile_nav.keyword_placeholder') }}"
                        data-mobile-search-input
                        autocomplete="off"
                    >
                    <button type="button" data-mobile-search-clear class="hidden inline-flex h-7 w-7 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100" aria-label="Temizle">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round">
                            <path d="M6 6l12 12M18 6 6 18"/>
                        </svg>
                    </button>
                </label>
            </form>

            <button type="button" data-mobile-search-close class="inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-100 hover:text-slate-900" aria-label="Kapat">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round">
                    <path d="M6 6l12 12M18 6 6 18"/>
                </svg>
            </button>
        </div>

        <div class="mt-3 overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_20px_40px_-26px_rgba(15,23,42,0.22)]">
            <div class="max-h-[calc(100vh-126px)] overflow-y-auto px-2 py-2" data-mobile-search-results>
                <p class="px-4 py-5 text-sm text-slate-500">{{ __('site.mobile_nav.empty_query') }}</p>
            </div>
            <button type="button" data-mobile-search-all class="hidden flex w-full items-center gap-2 border-t border-slate-200 px-4 py-3 text-left text-sm font-medium text-slate-900 transition hover:bg-slate-100">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 7H5v4"/>
                    <path d="M5 11a7 7 0 0 0 12 4l2-2"/>
                </svg>
                <span data-mobile-search-all-label>{{ __('site.mobile_nav.all_results') }}</span>
            </button>
        </div>
    </div>
</div>
