@extends('layouts.app')

@section('title', 'Kategori Olustur')
@section('meta_description', 'Yeni kategori olusturun.')

@section('content')
    <div class="mx-auto max-w-[45rem] rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Kategori Olustur</h1>
                <p class="mt-1 text-sm text-slate-500">Toplulukta yeni bir alan acin.</p>
            </div>
            <a href="{{ route('blog.categories') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                Kategorilere don
            </a>
        </div>

        <form action="{{ route('blog.category.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Kategori adi</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    maxlength="255"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-slate-300 focus:ring-0"
                >
                @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="mb-1 block text-sm font-medium text-slate-700">Slug (opsiyonel)</label>
                <input
                    id="slug"
                    type="text"
                    name="slug"
                    value="{{ old('slug') }}"
                    maxlength="255"
                    placeholder="ornek-kategori"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-slate-300 focus:ring-0"
                >
                <p class="mt-1 text-xs text-slate-500">Sadece kucuk harf, rakam ve tire kullanin. Bos birakirsan isimden otomatik uretilir.</p>
                @error('slug')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Aciklama</label>
                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-slate-300 focus:ring-0"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="profile_image" class="mb-1 block text-sm font-medium text-slate-700">Profil gorseli</label>
                    <input id="profile_image" type="file" name="profile_image" accept="image/*" class="block w-full text-sm text-slate-600">
                    <p class="mt-1 text-xs text-slate-500">PNG/JPG/WEBP - max 10MB</p>
                    @error('profile_image')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="cover_image" class="mb-1 block text-sm font-medium text-slate-700">Kapak gorseli</label>
                    <input id="cover_image" type="file" name="cover_image" accept="image/*" class="block w-full text-sm text-slate-600">
                    <p class="mt-1 text-xs text-slate-500">PNG/JPG/WEBP - max 15MB</p>
                    @error('cover_image')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <a href="{{ route('blog.categories') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    Iptal
                </a>
                <button type="submit" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Kategoriyi olustur
                </button>
            </div>
        </form>
    </div>
@endsection
