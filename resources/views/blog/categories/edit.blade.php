@extends('layouts.app')

@section('title', 'Kategori Duzenle')
@section('meta_description', 'OGrafi kategori duzenleme sayfasi.')

@section('content')
    <div class="mx-auto max-w-[45rem] px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h1 class="text-lg font-bold">Kategori Duzenle</h1>
            <a href="{{ route('blog.category', $category) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                Geri don
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <div class="font-semibold">Duzeltmeniz gereken alanlar var:</div>
                <ul class="mt-2 list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('blog.category.update', $category) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="rounded-2xl p-5 shadow-sm ring-1 ring-slate-200 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Isim</label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $category->name) }}"
                           required
                           class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Aciklama</label>
                    <textarea name="description"
                              rows="4"
                              class="w-full rounded-xl px-3 py-2 text-sm text-slate-900 outline-none"
                    >{{ old('description', $category->description) }}</textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl p-5 shadow-sm ring-1 ring-slate-200 space-y-3">
                    <div class="text-sm font-bold text-slate-900">Profil Resmi</div>
                    <div class="h-24 w-24 overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
                        @if($category->profile_image_url)
                            <img src="{{ $category->profile_image_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover" loading="lazy">
                        @endif
                    </div>
                    <input type="file" name="profile_image" accept="image/*" class="block w-full text-sm text-slate-700">
                    <div class="text-xs text-slate-500">PNG/JPG/WEBP - max 10MB</div>
                </div>

                <div class="rounded-2xl p-5 shadow-sm ring-1 ring-slate-200 space-y-3">
                    <div class="text-sm font-bold text-slate-900">Kapak Fotografi</div>
                    <div class="h-24 w-full overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
                        @if($category->cover_image_url)
                            <img src="{{ $category->cover_image_url }}" alt="{{ $category->name }}" class="h-full w-full object-cover" loading="lazy">
                        @endif
                    </div>
                    <input type="file" name="cover_image" accept="image/*" class="block w-full text-sm text-slate-700">
                    <div class="text-xs text-slate-500">PNG/JPG/WEBP - max 15MB</div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('blog.category', $category) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Vazgec
                </a>
                <button type="submit" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    Kaydet
                </button>
            </div>
        </form>
    </div>
@endsection


