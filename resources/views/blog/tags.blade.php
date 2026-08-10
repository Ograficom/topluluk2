@extends('layouts.app')

@section('title', __('site.tags_page.title'))

@section('content')
    @php
        $themeTags = \App\Models\ThemeSetting::render('tags');
        $sort = $sort ?? 'popular';
        $sortOptions = [
            'popular' => ['label' => 'Popüler', 'icon' => 'lucide:flame'],
            'newest' => ['label' => 'Yeni', 'icon' => 'lucide:sparkles'],
            'oldest' => ['label' => 'Eski', 'icon' => 'lucide:history'],
        ];
    @endphp
    @if ($themeTags !== '')
        <div class="mb-4">
            {!! $themeTags !!}
        </div>
    @endif

    <style>
        /* Siralama tetikleyicisi artik bagimsiz bir kutu degil, page-title-identity
           kutusunun ("trailing" slotu) icinde, kutunun sag ucunda duruyor. */
        .tags-sort {
            position: relative;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
            margin-left: auto;
        }

        .tags-sort__trigger {
            display: inline-flex;
            flex: 0 0 auto;
            width: 26px;
            height: 26px;
            align-items: center;
            justify-content: center;
            border: 0 !important;
            border-radius: 999px;
            background: transparent !important;
            color: inherit !important;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .tags-sort__trigger svg {
            width: 15px;
            height: 15px;
            pointer-events: none;
        }

        .tags-sort__trigger:hover,
        .tags-sort__trigger:focus-visible,
        .tags-sort.is-open .tags-sort__trigger {
            background: rgba(15, 15, 18, .06) !important;
            outline: none;
        }

        html.dark .tags-sort__trigger:hover,
        html.dark .tags-sort.is-open .tags-sort__trigger {
            background: rgba(255, 255, 255, .1) !important;
        }

        .tags-sort__menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 168px;
            border-radius: 14px;
            border: 1px solid #e4e4e7;
            background: #ffffff;
            padding: 6px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
            z-index: 40;
        }

        .tags-sort__menu[hidden] {
            display: none !important;
        }

        .tags-sort__options {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .tags-sort__option {
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 36px;
            padding: 0 10px;
            border-radius: 9px;
            color: #3f3f46;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color .15s ease, color .15s ease;
        }

        .tags-sort__option iconify-icon {
            font-size: 15px;
            flex-shrink: 0;
        }

        .tags-sort__option[aria-current="true"],
        .tags-sort__option:hover,
        .tags-sort__option:focus-visible {
            background: #f3f4f6;
            color: #0f172a;
            outline: none;
        }

        html.dark .tags-sort__menu {
            background: #18181b;
            border-color: #27272a;
        }

        html.dark .tags-sort__option {
            color: #d4d4d8;
        }

        html.dark .tags-sort__option[aria-current="true"],
        html.dark .tags-sort__option:hover,
        html.dark .tags-sort__option:focus-visible {
            background: #27272a;
            color: #f4f4f5;
        }

        .tag-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, .9);
            background: #ffffff;
            text-decoration: none;
            transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
        }

        .tag-row:hover,
        .tag-row:focus-visible {
            background: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, .06);
            outline: none;
        }

        .tag-row__name {
            font-weight: 600;
            color: #0f172a;
        }

        .tag-row__count {
            flex-shrink: 0;
            font-size: 13px;
            color: #64748b;
        }

        html.dark .tag-row {
            background: #18181b;
            border-color: #27272a;
        }

        html.dark .tag-row:hover {
            background: #1f1f23;
        }

        html.dark .tag-row__name {
            color: #f4f4f5;
        }

        html.dark .tag-row__count {
            color: #a1a1aa;
        }

        /* Yukleme sirasinda gercek satirla ayni boyutta dalgali (shimmer)
           iskelet kutu - sayfadaki gorsellerle ayni ografiImgWave animasyonunu
           kullanir (bkz. layouts/app.blade.php), boylece tum site tek bir
           yukleme dili paylasir. */
        .tag-row--skeleton {
            min-height: 54px;
            border-color: transparent;
            background: linear-gradient(105deg, #eef2fb 0%, #ffffff 45%, #eef2fb 82%);
            background-size: 200% 100%;
            animation: ografiImgWave 1.15s ease-in-out infinite;
            pointer-events: none;
        }

        html.dark .tag-row--skeleton {
            background: linear-gradient(105deg, #18181b 0%, #27272a 45%, #18181b 82%);
            background-size: 200% 100%;
        }
    </style>

    <div class="space-y-4">
        @include('partials.page-title-identity', [
            'title' => __('site.tags_page.title'),
            'trailing' => view('blog.partials.tags-sort-trigger', ['sort' => $sort, 'sortOptions' => $sortOptions])->render(),
        ])

        @foreach ($tags as $tag)
            <a
                href="{{ route('blog.index', ['tag' => $tag->slug]) }}"
                class="tag-row"
                data-tag-row
            >
                <span class="tag-row__name">#{{ $tag->name }}</span>
                <span class="tag-row__count">{{ number_format($tag->posts_count) }}</span>
            </a>
        @endforeach

        @include('partials.tags-load-more', ['tags' => $tags])
    </div>

    @push('scripts')
        <script>
            (() => {
                const root = document.querySelector('[data-tags-sort]');
                const trigger = document.querySelector('[data-tags-sort-trigger]');
                const menu = document.querySelector('[data-tags-sort-menu]');

                if (root && trigger && menu) {
                    const openMenu = () => {
                        root.classList.add('is-open');
                        menu.hidden = false;
                        trigger.setAttribute('aria-expanded', 'true');
                    };

                    const closeMenu = () => {
                        root.classList.remove('is-open');
                        menu.hidden = true;
                        trigger.setAttribute('aria-expanded', 'false');
                    };

                    trigger.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        if (menu.hidden) openMenu(); else closeMenu();
                    });

                    root.addEventListener('click', (event) => event.stopPropagation());
                    document.addEventListener('click', closeMenu);
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') closeMenu();
                    });
                }
            })();

            (() => {
                const nextSelector = '[data-tags-load-next]';
                const controlsSelector = '[data-tags-load-more]';
                const rowSelector = '[data-tag-row]';

                const rowKey = (row) => row.getAttribute('href') || '';

                const buildSkeletonRow = () => {
                    const row = document.createElement('div');
                    row.className = 'tag-row tag-row--skeleton';
                    row.setAttribute('aria-hidden', 'true');
                    return row;
                };

                document.addEventListener('click', async (event) => {
                    const button = event.target instanceof Element ? event.target.closest(nextSelector) : null;
                    if (!button) return;

                    event.preventDefault();

                    if (button.dataset.loading === '1') return;

                    const controls = button.closest(controlsSelector);
                    const parent = controls ? controls.parentElement : null;
                    const url = button.getAttribute('href');

                    if (!controls || !parent || !url) {
                        window.location.href = button.href;
                        return;
                    }

                    button.dataset.loading = '1';
                    button.classList.add('is-loading');
                    button.setAttribute('aria-busy', 'true');

                    const skeletons = Array.from({ length: 6 }, buildSkeletonRow);
                    skeletons.forEach((row) => parent.insertBefore(row, controls));

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                Accept: 'text/html, application/xhtml+xml',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            throw new Error('Etiket istegi basarisiz: ' + response.status);
                        }

                        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                        const currentKeys = new Set(
                            Array.from(document.querySelectorAll(rowSelector)).map(rowKey).filter(Boolean)
                        );
                        const fragment = document.createDocumentFragment();

                        Array.from(doc.querySelectorAll(rowSelector)).forEach((row) => {
                            const key = rowKey(row);
                            if (!key || currentKeys.has(key)) return;
                            currentKeys.add(key);
                            fragment.appendChild(row);
                        });

                        skeletons.forEach((row) => row.remove());
                        parent.insertBefore(fragment, controls);

                        const nextControls = doc.querySelector(controlsSelector);
                        if (nextControls) {
                            controls.replaceWith(nextControls);
                        } else {
                            controls.remove();
                        }
                    } catch (error) {
                        skeletons.forEach((row) => row.remove());
                        window.location.href = button.href;
                    }
                }, true);
            })();
        </script>
    @endpush
@endsection
