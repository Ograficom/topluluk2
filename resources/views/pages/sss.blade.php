@extends('layouts.app')

@php
    $faqSiteName = trim((string) config('app.name', 'Ografi'));
    $faqPageTitle = 'Sıkça Sorulan Sorular';
    $faqPageDescription = 'Sıkça sorulan sorular ve cevaplar.';
    $faqCanonicalUrl = route('pages.sss');
    $faqSearch = trim((string) request()->query('q', ''));
    $faqSort = (string) request()->query('sort', 'ordered');
    $faqSortOptions = [
        'ordered' => ['label' => 'Önerilen', 'icon' => 'lucide:list-filter'],
        'newest' => ['label' => 'Yeni', 'icon' => 'lucide:sparkles'],
        'oldest' => ['label' => 'Eski', 'icon' => 'lucide:history'],
    ];

    if (! array_key_exists($faqSort, $faqSortOptions)) {
        $faqSort = 'ordered';
    }
@endphp

@section('title', $faqPageTitle)
@section('meta_description', $faqPageDescription)
@section('canonical_url', $faqCanonicalUrl)

@push('seo')
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ e($faqSiteName !== '' ? $faqSiteName : 'Ografi') }}">
<meta property="og:title" content="{{ e($faqPageTitle) }}">
<meta property="og:description" content="{{ e($faqPageDescription) }}">
<meta property="og:url" content="{{ e($faqCanonicalUrl) }}">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ e($faqPageTitle) }}">
<meta name="twitter:description" content="{{ e($faqPageDescription) }}">
@endpush

@php
    $allFaqItems = \App\Models\Faq::query()
        ->active()
        ->ordered()
        ->get(['id', 'question', 'answer', 'sort_order']);

    $normalizeText = function ($value): string {
        $text = trim((string) $value);

        if ($text === '') {
            return '';
        }

        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    };

    $faqItems = $allFaqItems;

    if ($faqSearch !== '') {
        $normalizedSearch = mb_strtolower($normalizeText($faqSearch), 'UTF-8');
        $faqItems = $faqItems->filter(function ($item) use ($normalizeText, $normalizedSearch): bool {
            $question = mb_strtolower(strip_tags($normalizeText($item->question ?? '')), 'UTF-8');
            $answer = mb_strtolower(strip_tags($normalizeText($item->answer ?? '')), 'UTF-8');

            return str_contains($question, $normalizedSearch) || str_contains($answer, $normalizedSearch);
        })->values();
    }

    $faqItems = match ($faqSort) {
        'newest' => $faqItems->sortByDesc('id')->values(),
        'oldest' => $faqItems->sortBy('id')->values(),
        default => $faqItems->sortBy([
            ['sort_order', 'asc'],
            ['id', 'asc'],
        ])->values(),
    };

    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $allFaqItems->map(function ($item) use ($normalizeText) {
            $question = is_array($item) ? ($item['question'] ?? '') : ($item->question ?? '');
            $answer = is_array($item) ? ($item['answer'] ?? '') : ($item->answer ?? '');

            $question = $normalizeText($question);
            $answer = $normalizeText($answer);

            if ($question === '' || $answer === '') {
                return null;
            }

            return [
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($answer),
                ],
            ];
        })->filter()->values()->all(),
    ];
@endphp

@push('head')
    <script type="application/ld+json">
        {!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <style>
        .ografi-faq-page {
            width: 100%;
        }

        .ografi-faq-list {
            width: 100%;
            padding-bottom: 0;
            transition: opacity .15s ease;
        }

        .ografi-faq-list.is-loading {
            opacity: .55;
        }

        /* Etiketler sayfasindaki arama ve siralama araclari. */
        .faq-toolbar {
            display: flex;
            align-items: center;
            gap: 2px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .faq-sort {
            position: relative;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }

        .faq-toolbar__icon.faq-toolbar__icon {
            display: inline-flex !important;
            flex: 0 0 auto;
            width: 32px !important;
            height: 32px !important;
            align-items: center;
            justify-content: center;
            border: 0 !important;
            border-radius: 999px !important;
            background: transparent !important;
            color: #52525b !important;
            cursor: pointer;
            transition: background-color .15s ease, transform .08s ease-out;
        }

        .faq-toolbar__icon iconify-icon {
            font-size: 16px;
        }

        .faq-toolbar__icon.faq-toolbar__icon:hover,
        .faq-toolbar__icon.faq-toolbar__icon:focus-visible,
        .faq-toolbar__icon.faq-toolbar__icon[aria-expanded="true"],
        .faq-sort.is-open .faq-toolbar__icon.faq-toolbar__icon {
            background: #f3f4f6 !important;
            outline: none;
        }

        .faq-toolbar__icon:active {
            transform: translateY(1px);
        }

        html.dark .faq-toolbar__icon {
            color: #cbd5e1 !important;
        }

        html.dark .faq-toolbar__icon.faq-toolbar__icon:hover,
        html.dark .faq-toolbar__icon.faq-toolbar__icon:focus-visible,
        html.dark .faq-toolbar__icon.faq-toolbar__icon[aria-expanded="true"],
        html.dark .faq-sort.is-open .faq-toolbar__icon.faq-toolbar__icon {
            background: #1e293b !important;
        }

        .faq-sort__menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            z-index: 40;
            width: 190px;
            padding: 8px;
            border: 1px solid #e4e4e7;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 16px 36px rgba(15, 23, 42, .14);
            visibility: hidden;
            opacity: 0;
            transform: scale(.96) translateY(-6px);
            transform-origin: top right;
            transition: opacity .16s ease, transform .16s ease, visibility 0s linear .16s;
        }

        .faq-sort__menu.is-open {
            visibility: visible;
            opacity: 1;
            transform: scale(1) translateY(0);
            transition: opacity .16s ease, transform .16s ease;
        }

        .faq-sort__label {
            display: block;
            margin: 4px 8px 6px;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .faq-sort__options {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .faq-sort__option {
            display: flex;
            min-height: 36px;
            align-items: center;
            gap: 8px;
            padding: 0 10px;
            border-radius: 10px;
            color: #3f3f46;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color .15s ease, color .15s ease;
        }

        .faq-sort__option iconify-icon {
            flex-shrink: 0;
            font-size: 15px;
        }

        .faq-sort__option[aria-current="true"],
        .faq-sort__option:hover,
        .faq-sort__option:focus-visible {
            background: #f3f4f6;
            color: #0f172a;
            outline: none;
        }

        .faq-sort__option[aria-current="true"] {
            color: #1d4ed8;
        }

        .faq-sort__option[aria-current="true"] iconify-icon {
            color: #2563eb;
        }

        html.dark .faq-sort__menu {
            border-color: #27272a;
            background: #18181b;
        }

        html.dark .faq-sort__label {
            color: #71717a;
        }

        html.dark .faq-sort__option {
            color: #d4d4d8;
        }

        html.dark .faq-sort__option[aria-current="true"],
        html.dark .faq-sort__option:hover,
        html.dark .faq-sort__option:focus-visible {
            background: #27272a;
            color: #f4f4f5;
        }

        html.dark .faq-sort__option[aria-current="true"],
        html.dark .faq-sort__option[aria-current="true"] iconify-icon {
            color: #93c5fd;
        }

        .faq-search-panel {
            position: relative;
            display: grid;
            grid-template-rows: 0fr;
            opacity: 0;
            transition: grid-template-rows .2s ease, opacity .15s ease;
        }

        .faq-search-panel.is-open {
            grid-template-rows: 1fr;
            opacity: 1;
        }

        .faq-search-panel__inner {
            min-height: 0;
            overflow: hidden;
        }

        .faq-search-panel__form {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 3px;
            padding: 4px 4px 4px 14px;
            border: 1px solid rgba(217, 221, 227, .78);
            border-radius: 999px;
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            box-shadow: 0 1px 0 rgba(255, 255, 255, .55) inset, 0 10px 28px rgba(15, 23, 42, .08);
        }

        input#faq-search-input.faq-search-panel__input.faq-search-panel__input {
            flex: 1 1 auto;
            min-width: 0;
            min-height: 0 !important;
            padding: 6px 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            color: #050505 !important;
            font-size: 14px;
            outline: none !important;
        }

        input#faq-search-input.faq-search-panel__input.faq-search-panel__input::placeholder {
            color: #9ca3af;
        }

        html.dark .faq-search-panel__form {
            border-color: rgba(63, 63, 70, .76);
            background: rgba(24, 24, 27, .82);
            box-shadow: 0 1px 0 rgba(255, 255, 255, .08) inset, 0 10px 28px rgba(0, 0, 0, .22);
        }

        html.dark input#faq-search-input.faq-search-panel__input.faq-search-panel__input {
            color: #fafafa !important;
        }

        /* Etiketler ile ayni opak 40px baslik kapsulu. */
        .page-title-identity.page-title-identity,
        .faq-page-title.faq-page-title {
            overflow: visible;
            border: 1px solid #e2e5ea !important;
            border-radius: 999px !important;
            background: #ffffff !important;
            background-color: #ffffff !important;
            background-image: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: none !important;
            filter: none !important;
            opacity: 1 !important;
        }

        .faq-page-title.faq-page-title {
            position: relative;
            display: flex !important;
            box-sizing: border-box !important;
            width: 100% !important;
            min-height: 40px !important;
            align-items: center !important;
            gap: 0 !important;
            padding: 2px 12px !important;
        }

        .faq-page-title.faq-page-title::before {
            content: '' !important;
            display: block !important;
            position: absolute;
            right: 0;
            bottom: 100%;
            left: 0;
            z-index: 0;
            width: 100%;
            height: 16px;
            background: rgba(246, 244, 240, .64);
            backdrop-filter: blur(12px) saturate(115%);
            -webkit-backdrop-filter: blur(12px) saturate(115%);
            pointer-events: none;
        }

        .faq-page-title__back,
        .faq-page-title__text,
        .faq-page-title__trailing {
            position: relative;
            z-index: 1;
        }

        .faq-page-title__back {
            display: inline-flex;
            flex: 0 0 auto;
            width: 34px;
            height: 30px;
            align-items: center;
            justify-content: flex-start;
            margin-right: 10px;
            padding: 0 10px 0 2px;
            border: 0 !important;
            border-right: 1px solid #e5e7eb !important;
            border-radius: 0 !important;
            background: #ffffff !important;
            color: #111827 !important;
            text-decoration: none !important;
        }

        .faq-page-title__back iconify-icon {
            font-size: 17px;
        }

        .faq-page-title__text {
            min-width: 0;
            overflow: hidden;
            color: #111111 !important;
            font-size: 18px;
            font-weight: 500;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .faq-page-title__trailing {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        body.route-sss [data-faq-search-panel] {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        body.route-sss [data-faq-search-panel]::before {
            display: none !important;
            content: none !important;
        }

        body.route-sss [data-faq-search-panel].is-open::before {
            content: '' !important;
            display: block !important;
            position: absolute;
            right: 0;
            bottom: 100%;
            left: 0;
            z-index: 0;
            width: 100%;
            height: 16px;
            background: rgba(246, 244, 240, .64);
            backdrop-filter: blur(12px) saturate(115%);
            -webkit-backdrop-filter: blur(12px) saturate(115%);
            pointer-events: none;
        }

        body.route-sss [data-faq-search-panel] > * {
            position: relative;
            z-index: 1;
        }

        body.route-sss .faq-search-panel__inner,
        body.route-sss .faq-search-panel__form {
            box-sizing: border-box;
            width: 100%;
            max-width: 100%;
        }

        html.dark .faq-page-title.faq-page-title {
            border-color: #27272a !important;
            background: #18181b !important;
            background-color: #18181b !important;
        }

        html.dark .faq-page-title__back {
            border-right-color: #27272a !important;
            background: #18181b !important;
            color: #f4f4f5 !important;
        }

        html.dark .faq-page-title__text {
            color: #f4f4f5 !important;
        }

        @media (max-width: 640px) {
            body.route-sss [data-faq-search-panel] {
                padding-top: 3px;
                padding-bottom: 5px;
                overflow: visible;
                isolation: isolate;
                box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
            }

            body.route-sss [data-faq-search-panel]:not(.is-open) {
                display: none;
                height: 0;
                min-height: 0;
                padding-top: 0;
                padding-bottom: 0;
                box-shadow: none;
            }

            body.route-sss [data-faq-search-panel]::after {
                content: '';
                position: absolute;
                z-index: 0;
                inset: 0;
                border-top: 1px solid rgba(255, 255, 255, .46);
                border-bottom: 2px solid rgba(255, 255, 255, .66);
                box-shadow: 0 10px 22px rgba(15, 23, 42, .06);
                pointer-events: none;
            }

            html.dark body.route-sss [data-faq-search-panel] {
                box-shadow: 0 8px 24px rgba(0, 0, 0, .22);
            }
        }

        @media (min-width: 641px) {
            body.route-sss .site-header {
                position: sticky;
                top: 0;
                z-index: 50;
            }

            body.route-sss .page-title-identity {
                position: sticky;
                top: 64px;
                z-index: 30;
            }

            body.route-sss [data-faq-search-panel] {
                position: sticky;
                top: 104px;
                z-index: 29;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .faq-search-panel,
            .faq-sort__menu,
            .ografi-faq-list {
                transition: none;
            }
        }

        @media (prefers-reduced-transparency: reduce) {
            .faq-search-panel__form {
                background: #ffffff;
                backdrop-filter: none;
                -webkit-backdrop-filter: none;
            }

            html.dark .faq-search-panel__form {
                background: #18181b;
            }
        }

        .ografi-faq-item {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            color: #0f172a;
            transition:
                background-color 180ms ease,
                border-color 180ms ease,
                color 180ms ease;
        }

        .ografi-faq-summary {
            color: #0f172a;
        }

        .ografi-faq-icon {
            color: #64748b;
        }

        .ografi-faq-answer {
            border-top: 1px solid rgba(226, 232, 240, 0.95);
            color: #475569;
        }

        .ografi-faq-empty {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            color: #475569;
        }

        html.dark .ografi-faq-item,
        .dark .ografi-faq-item,
        body.dark .ografi-faq-item,
        [data-theme="dark"] .ografi-faq-item,
        [data-bs-theme="dark"] .ografi-faq-item {
            background: #0f172a !important;
            border-color: rgba(51, 65, 85, 0.95) !important;
            color: #f8fafc !important;
        }

        html.dark .ografi-faq-summary,
        .dark .ografi-faq-summary,
        body.dark .ografi-faq-summary,
        [data-theme="dark"] .ografi-faq-summary,
        [data-bs-theme="dark"] .ografi-faq-summary {
            color: #f8fafc !important;
        }

        html.dark .ografi-faq-icon,
        .dark .ografi-faq-icon,
        body.dark .ografi-faq-icon,
        [data-theme="dark"] .ografi-faq-icon,
        [data-bs-theme="dark"] .ografi-faq-icon {
            color: #cbd5e1 !important;
        }

        html.dark .ografi-faq-answer,
        .dark .ografi-faq-answer,
        body.dark .ografi-faq-answer,
        [data-theme="dark"] .ografi-faq-answer,
        [data-bs-theme="dark"] .ografi-faq-answer {
            border-top-color: rgba(51, 65, 85, 0.95) !important;
            color: #cbd5e1 !important;
        }

        html.dark .ografi-faq-empty,
        .dark .ografi-faq-empty,
        body.dark .ografi-faq-empty,
        [data-theme="dark"] .ografi-faq-empty,
        [data-bs-theme="dark"] .ografi-faq-empty {
            background: #0f172a !important;
            border-color: rgba(51, 65, 85, 0.95) !important;
            color: #cbd5e1 !important;
        }

        @media (prefers-color-scheme: dark) {
            html:not(.light) .ografi-faq-item {
                background: #0f172a;
                border-color: rgba(51, 65, 85, 0.95);
                color: #f8fafc;
            }

            html:not(.light) .ografi-faq-summary {
                color: #f8fafc;
            }

            html:not(.light) .ografi-faq-icon {
                color: #cbd5e1;
            }

            html:not(.light) .ografi-faq-answer {
                border-top-color: rgba(51, 65, 85, 0.95);
                color: #cbd5e1;
            }

            html:not(.light) .ografi-faq-empty {
                background: #0f172a;
                border-color: rgba(51, 65, 85, 0.95);
                color: #cbd5e1;
            }
        }

        @media (max-width: 640px) {
            .ografi-faq-list {
                gap: 0.75rem;
                padding-bottom: calc(112px + env(safe-area-inset-bottom, 0px)) !important;
            }

            .ografi-faq-item,
            .ografi-faq-empty {
                border-radius: 1rem !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="ografi-faq-page space-y-4">
        <div class="page-title-identity faq-page-title">
            <a href="{{ url()->previous() }}" class="faq-page-title__back" aria-label="Geri">
                <iconify-icon icon="lucide:arrow-left" aria-hidden="true"></iconify-icon>
            </a>
            <span class="faq-page-title__text">{{ $faqPageTitle }}</span>
            <div class="faq-page-title__trailing">
                @include('pages.partials.faq-toolbar', [
                    'faqSort' => $faqSort,
                    'faqSearch' => $faqSearch,
                    'faqSortOptions' => $faqSortOptions,
                ])
            </div>
        </div>

        <div id="faq-search-panel" class="faq-search-panel {{ $faqSearch !== '' ? 'is-open' : '' }}" data-faq-search-panel>
            <div class="faq-search-panel__inner">
                <form method="GET" action="{{ route('pages.sss') }}" class="faq-search-panel__form" data-faq-search-form>
                    @if ($faqSort !== 'ordered')
                        <input type="hidden" name="sort" value="{{ $faqSort }}">
                    @endif
                    <input
                        type="search"
                        id="faq-search-input"
                        name="q"
                        value="{{ $faqSearch }}"
                        placeholder="Sıkça sorulan sorularda ara"
                        class="faq-search-panel__input"
                        autocomplete="off"
                        data-faq-search-input
                    >
                    <button type="submit" class="faq-toolbar__icon" aria-label="SSS içinde ara">
                        <iconify-icon icon="lucide:search" aria-hidden="true"></iconify-icon>
                    </button>
                </form>
                <p class="sr-only" role="status" aria-live="polite" data-faq-status></p>
            </div>
        </div>
        <div data-search-panel-spacer aria-hidden="true"></div>

        <section class="ografi-faq-list flex flex-col gap-3 sm:gap-4" data-faq-list data-total="{{ $faqItems->count() }}">
            @foreach($faqItems as $item)
                @php
                    $question = is_array($item) ? ($item['question'] ?? '') : ($item->question ?? '');
                    $answer = is_array($item) ? ($item['answer'] ?? '') : ($item->answer ?? '');

                    $question = $normalizeText($question);
                    $answer = $normalizeText($answer);
                @endphp

                @if($question !== '' && $answer !== '')
                    <article class="ografi-faq-item rounded-2xl px-4 py-4 sm:px-5 sm:py-5" data-faq-item>
                        <details class="group">
                            <summary class="ografi-faq-summary flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-normal">
                                <span>{{ $question }}</span>

                                <span class="ografi-faq-icon shrink-0 text-lg leading-none transition-transform duration-200 group-open:rotate-45">
                                    +
                                </span>
                            </summary>

                            <div class="ografi-faq-answer mt-3 pt-3 text-sm font-normal leading-6">
                                {!! nl2br(e($answer)) !!}
                            </div>
                        </details>
                    </article>
                @endif
            @endforeach

            @if($faqItems->isEmpty())
                <div class="ografi-faq-empty rounded-xl px-4 py-3 text-sm font-normal">
                    {{ $faqSearch !== '' ? 'Aramanızla eşleşen bir SSS bulunamadı.' : 'Henüz admin panelinden aktif bir SSS eklenmedi.' }}
                </div>
            @endif
        </section>
    </div>

    @push('scripts')
        <script>
            (() => {
                const panel = document.querySelector('[data-faq-search-panel]');
                const trigger = document.querySelector('[data-faq-search-trigger]');
                const input = document.querySelector('[data-faq-search-input]');
                const inner = panel?.querySelector('.faq-search-panel__inner');
                const panelSpacer = document.querySelector('[data-search-panel-spacer]');
                const identity = document.querySelector('.page-title-identity');

                const syncPanelGeometry = () => {
                    if (!panel || !identity) return;

                    if (window.getComputedStyle(panel).position === 'fixed') {
                        const identityRect = identity.getBoundingClientRect();
                        panel.style.setProperty('left', `${identityRect.left}px`, 'important');
                        panel.style.setProperty('right', 'auto', 'important');
                        panel.style.setProperty('width', `${identityRect.width}px`, 'important');
                        panel.style.setProperty('max-width', `${identityRect.width}px`, 'important');
                    } else {
                        panel.style.removeProperty('left');
                        panel.style.removeProperty('right');
                        panel.style.removeProperty('width');
                        panel.style.removeProperty('max-width');
                    }
                };

                const syncPanelSpacer = (open) => {
                    if (!panelSpacer || !inner) return;
                    panelSpacer.style.height = open ? `${inner.scrollHeight}px` : '0px';
                };

                if (panel && trigger && input) {
                    window.requestAnimationFrame(syncPanelGeometry);

                    if (panel.classList.contains('is-open')) {
                        syncPanelSpacer(true);
                    }

                    trigger.addEventListener('click', () => {
                        const willOpen = !panel.classList.contains('is-open');
                        panel.classList.toggle('is-open', willOpen);
                        trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                        syncPanelSpacer(willOpen);
                        window.requestAnimationFrame(syncPanelGeometry);

                        if (willOpen) {
                            window.requestAnimationFrame(() => input.focus());
                        }
                    });

                    window.addEventListener('resize', syncPanelGeometry, { passive: true });
                    window.addEventListener('scroll', syncPanelGeometry, { passive: true });

                    if ('ResizeObserver' in window && identity) {
                        new ResizeObserver(syncPanelGeometry).observe(identity);
                    }
                }
            })();

            (() => {
                const root = document.querySelector('[data-faq-sort]');
                const trigger = document.querySelector('[data-faq-sort-trigger]');
                const menu = document.querySelector('[data-faq-sort-menu]');

                if (!root || !trigger || !menu) return;

                const closeMenu = () => {
                    root.classList.remove('is-open');
                    menu.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                };

                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const willOpen = !menu.classList.contains('is-open');
                    root.classList.toggle('is-open', willOpen);
                    menu.classList.toggle('is-open', willOpen);
                    trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                });

                root.addEventListener('click', (event) => event.stopPropagation());
                document.addEventListener('click', closeMenu);
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') closeMenu();
                });
            })();

            (() => {
                const form = document.querySelector('[data-faq-search-form]');
                const input = document.querySelector('[data-faq-search-input]');
                const list = document.querySelector('[data-faq-list]');
                const status = document.querySelector('[data-faq-status]');

                if (!form || !input || !list) return;

                let debounceTimer = null;
                let activeController = null;

                const runSearch = async () => {
                    if (activeController) activeController.abort();
                    const controller = new AbortController();
                    activeController = controller;

                    const url = new URL(form.action, window.location.origin);
                    const formData = new FormData(form);
                    for (const [key, value] of formData.entries()) {
                        if (value !== '') url.searchParams.set(key, value);
                        else url.searchParams.delete(key);
                    }

                    list.classList.add('is-loading');

                    try {
                        const response = await fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html, application/xhtml+xml' },
                            credentials: 'same-origin',
                            signal: controller.signal,
                        });

                        if (!response.ok) throw new Error(`SSS araması başarısız: ${response.status}`);

                        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                        const newList = doc.querySelector('[data-faq-list]');

                        if (newList) {
                            list.innerHTML = newList.innerHTML;
                            list.dataset.total = newList.dataset.total || '0';
                        }

                        if (status) {
                            status.textContent = `${list.dataset.total || '0'} SSS bulundu`;
                        }

                        window.history.replaceState(null, '', url.toString());
                    } catch (error) {
                        if (error.name !== 'AbortError') console.error(error);
                    } finally {
                        if (activeController === controller) {
                            list.classList.remove('is-loading');
                            activeController = null;
                        }
                    }
                };

                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(runSearch, 300);
                });

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    clearTimeout(debounceTimer);
                    runSearch();
                });
            })();
        </script>
    @endpush
@endsection
