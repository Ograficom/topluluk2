@extends('layouts.app')

@section('title', __('site.tags_page.title'))

@section('content')
    @php($themeTags = \App\Models\ThemeSetting::render('tags'))
    @if ($themeTags !== '')
        <div class="mb-4">
            {!! $themeTags !!}
        </div>
    @endif

    <style>
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
        <section class="space-y-4">
            @include('partials.page-title-identity', ['title' => __('site.tags_page.title')])
        </section>

        @foreach ($tags as $tag)
            <a
                href="{{ route('blog.index', ['tag' => $tag->slug]) }}"
                class="tag-row"
                data-tag-row
            >
                <span class="tag-row__name">#{{ $tag->name }}</span>
                <span class="tag-row__count">{{ __('site.tags_page.posts_count', ['count' => $tag->posts_count]) }}</span>
            </a>
        @endforeach

        @include('partials.tags-load-more', ['tags' => $tags])
    </div>

    @push('scripts')
        <script>
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
