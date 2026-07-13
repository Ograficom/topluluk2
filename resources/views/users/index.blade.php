@extends('layouts.app')

@section('title', __('site.users.title'))
@section('meta_description', __('site.users.meta_description'))

@section('content')
    <div class="rounded-3xl bg-white p-4 shadow-sm ring-1 ring-slate-200/80 sm:p-5">
        <h1 class="text-xl font-semibold text-slate-900">{{ __('site.users.title') }}</h1>
    </div>

    <div class="mt-2 grid grid-cols-1 gap-2">
        @forelse ($users as $user)
            <div class="rounded-[26px] bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200/80">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('users.show', $user) }}" class="flex min-w-0 items-center gap-3">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-12 w-12 rounded-full object-cover">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1">
                                <p class="truncate text-lg font-semibold leading-tight text-slate-900">{{ $user->name }}</p>
                                <x-verification-badge :user="$user" class="inline-flex h-4 w-4 shrink-0 items-center justify-center" size="sm" />
                            </div>
                            <p class="truncate text-sm text-slate-500">{{ '@' . ($user->username ?: __('site.users.default_username')) }}</p>
                        </div>
                    </a>

                    @auth
                        @if(auth()->id() !== $user->id)
                            <form method="POST" action="{{ route('users.follow', $user) }}" class="m-0 shrink-0">
                                @csrf
                                <button type="submit" class="rounded-full bg-slate-100 px-5 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                                    {{ (bool) ($user->is_followed_by_viewer ?? false) ? 'Takiptesin' : 'Takip et' }}
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="shrink-0 rounded-full bg-slate-100 px-5 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                            Takip et
                        </a>
                    @endauth
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white p-6 text-sm text-slate-500 shadow-sm ring-1 ring-slate-200/80">
                {{ __('site.users.empty') }}
            </div>
        @endforelse
    </div>

    <div class="mt-2 flex items-center justify-between gap-3 text-xs text-slate-500">
        <span>Sayfa basina {{ $users->perPage() }} kullanici</span>
        {{ $users->links() }}
    </div>
@endsection

