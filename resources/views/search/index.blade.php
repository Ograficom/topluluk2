@extends('layouts.app')

@section('title', __('site.search.title'))
@section('meta_description', __('site.search.meta_description'))

@section('content')
    <div class="space-y-8">
        <div class="alma-panel p-4 sm:p-6">
            <div class="alma-toolbar">
                <form action="{{ route('search') }}" method="GET" class="relative flex-1 min-w-0">
                    <input type="text"
                           name="q"
                           value="{{ $query }}"
                           placeholder="{{ __('site.search.placeholder') }}"
                           class="alma-input w-full pl-11"
                           autofocus>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                         class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400">
                        <path d="M13.78 12.72a6 6 0 10-1.06 1.06l3.75 3.75a.75.75 0 101.06-1.06l-3.75-3.75zM12 9a5 5 0 11-10 0 5 5 0 0110 0z" fill="currentColor"/>
                    </svg>
                </form>
                <a href="{{ url('/') }}" class="alma-button-secondary">
                    {{ __('site.search.back_home') }}
                </a>
            </div>
        </div>

        @if (!$meta['enabled'])
            <div class="alma-panel p-6 text-sm text-slate-600">
                {{ __('site.search.disabled') }}
            </div>
        @elseif($meta['too_short'])
            <div class="alma-panel p-6 text-sm text-slate-600">
                {{ __('site.search.too_short', ['min' => $meta['min_length']]) }}
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-2">
                <section class="alma-panel p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('site.search.posts') }}</h2>
                        <span class="text-xs text-slate-400">{{ __('site.common.result_count', ['count' => count($results['posts'])]) }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($results['posts'] as $item)
                            <a href="{{ $item['url'] }}" class="block rounded-2xl border border-slate-200/80 bg-slate-50/70 px-4 py-3 hover:bg-white">
                                <p class="text-sm font-semibold text-slate-900">{{ $item['title'] }}</p>
                                @if(!empty($item['snippet']))
                                    <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $item['snippet'] }}</p>
                                @endif
                                <div class="mt-2 flex items-center gap-3 text-[11px] text-slate-500">
                                    @if($item['category'])
                                        <span>{{ $item['category'] }}</span>
                                    @endif
                                    @if($item['author'])
                                        <span>- {{ $item['author'] }}</span>
                                    @endif
                                    <span>- {{ __('site.search.views', ['count' => number_format($item['views'])]) }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('site.search.empty') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="alma-panel p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('site.search.categories') }}</h2>
                        <span class="text-xs text-slate-400">{{ __('site.common.result_count', ['count' => count($results['categories'])]) }}</span>
                    </div>
                    <div class="space-y-2">
                        @forelse($results['categories'] as $item)
                            <a href="{{ $item['url'] }}" class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-slate-50/70 px-4 py-2.5 hover:bg-white">
                                <span class="text-sm font-semibold text-slate-900">{{ $item['title'] }}</span>
                                <span class="text-xs text-slate-500">{{ __('site.search.category_badge') }}</span>
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('site.search.empty') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="alma-panel p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('site.search.tags') }}</h2>
                        <span class="text-xs text-slate-400">{{ __('site.common.result_count', ['count' => count($results['tags'])]) }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($results['tags'] as $item)
                            <a href="{{ $item['url'] }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-slate-50/70 px-3 py-1 text-sm text-slate-800 hover:bg-white">
                                #{{ $item['title'] }}
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('site.search.empty') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="alma-panel p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('site.search.users') }}</h2>
                        <span class="text-xs text-slate-400">{{ __('site.common.result_count', ['count' => count($results['users'])]) }}</span>
                    </div>
                    <div class="space-y-2">
                        @forelse($results['users'] as $item)
                            @php($userUrl = $item['url'] ?? null)
                            <a href="{{ $userUrl ?? '#' }}" class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-slate-50/70 px-4 py-2.5 hover:bg-white {{ $userUrl ? '' : 'cursor-default' }}">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $item['title'] }}</p>
                                    @if(!empty($item['subtitle']))
                                        <p class="text-xs text-slate-500">{{ $item['subtitle'] }}</p>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400">{{ __('site.search.profile_badge') }}</span>
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('site.search.empty') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="alma-panel p-5 space-y-3 lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('site.search.pages') }}</h2>
                        <span class="text-xs text-slate-400">{{ __('site.common.result_count', ['count' => count($results['pages'])]) }}</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($results['pages'] as $item)
                            <a href="{{ $item['url'] }}" class="block rounded-2xl border border-slate-200/80 bg-slate-50/70 px-4 py-3 hover:bg-white">
                                <p class="text-sm font-semibold text-slate-900">{{ $item['title'] }}</p>
                                @if(!empty($item['snippet']))
                                    <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $item['snippet'] }}</p>
                                @endif
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('site.search.empty') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>
        @endif
    </div>
@endsection



