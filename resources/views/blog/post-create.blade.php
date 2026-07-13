@extends('layouts.app')

@section('title', 'Yeni Yazı')

@section('content')
    <div class="max-w-[45rem] mx-auto px-4 sm:px-0">
        <div class="bg-white text-slate-900 rounded-2xl shadow-xl overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-3">
                <h1 class="text-lg font-semibold">Gonderi Olustur</h1>
                <a href="{{ route('blog.index') }}" class="text-slate-500 hover:text-slate-700 text-xl leading-none">×</a>
            </div>
            <form method="POST" action="{{ route('blog.store') }}" class="p-4 sm:p-6 space-y-4">
                @csrf
                <div class="flex flex-wrap items-center gap-3">
                    @php($user = auth()->user())
                    @if ($user && method_exists($user, 'profile_photo_url'))
                        <img src="{{ $user->profile_photo_url }}" class="h-10 w-10 rounded-full object-cover" alt="{{ $user->name }}">
                    @else
                        <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-semibold">
                            {{ $user ? strtoupper(substr($user->name,0,1)) : 'U' }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-900 truncate">{{ $user->name ?? 'Misafir' }}</div>
                        <select class="text-xs rounded px-2 py-1 text-slate-700">
                            <option>Arkadaslar</option>
                            <option>Herkes</option>
                            <option>Sadece Ben</option>
                        </select>
                    </div>
                </div>

                <div>
                    <input type="text" name="title" placeholder="Başlık (zorunlu)" value="{{ old('title') }}"
                           class="w-full text-base sm:text-lg font-semibold rounded px-3 py-2 focus:outline-none" required>
                    @error('title')<div class="text-sm text-red-500 mt-1">{{ $message }}</div>@enderror
                </div>

                <div>
                    <textarea name="content" rows="6" data-mentionable="users" placeholder="Aklinizdan neler geciyor?"
                              class="w-full resize-none text-base sm:text-lg rounded px-3 py-2 focus:outline-none">{{ old('content') }}</textarea>
                    @error('content')<div class="text-sm text-red-500 mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-sm text-slate-600 mb-1 block">Kategori</label>
                        <select name="category_id" class="w-full rounded px-3 py-2 text-sm">
                            <option value="">Seçiniz</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="text-sm text-red-500 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-600 mb-1 block">Etiketler</label>
                        <select name="tags[]" multiple class="w-full rounded px-3 py-2 text-sm">
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}" @selected(collect(old('tags', []))->contains($tag->id))>#{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        @error('tags')<div class="text-sm text-red-500 mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between rounded px-3 py-2 text-sm text-slate-600 bg-slate-50">
                    <span>Gonderine ekle</span>
                    <div class="flex flex-wrap items-center gap-3 text-lg text-slate-500">
                        <span>🅰️</span>
                        <span>📹</span>
                        <span>🖼️</span>
                        <span>👥</span>
                        <span>😊</span>
                        <span>📍</span>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label class="inline-flex items-center text-sm text-slate-700 gap-2">
                        <input type="checkbox" name="is_published" value="1" class="rounded">
                        Yayınla
                    </label>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-full bg-amber-500 text-slate-900 font-semibold hover:bg-amber-400 text-center">
                        Paylas
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection 



