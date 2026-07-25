@extends('layouts.app')

@section('title', 'Kategori Olustur')
@section('meta_description', 'Yeni kategori olusturun.')

@section('content')
    <div class="mx-auto w-full max-w-2xl space-y-6 rounded-2xl border border-slate-100 bg-white p-6 shadow-xl shadow-slate-100/50 md:p-8">
        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kategori Oluştur</h1>
                <p class="mt-1 text-sm text-slate-500">Toplulukta yeni bir alan açın.</p>
            </div>
            <a href="{{ route('blog.categories') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                Kategorilere dön
            </a>
        </div>

        <form action="{{ route('blog.category.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">Kategori adı</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    maxlength="255"
                    placeholder="Örn: Yazılım &amp; Teknoloji"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                >
                @error('name')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="mb-1.5 block text-sm font-semibold text-slate-700">
                    Slug <span class="text-xs font-normal text-slate-400">(opsiyonel)</span>
                </label>
                <input
                    id="slug"
                    type="text"
                    name="slug"
                    value="{{ old('slug') }}"
                    maxlength="255"
                    placeholder="ornek-kategori"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                >
                <p class="mt-1.5 text-xs text-slate-400">Sadece küçük harf, rakam ve tire kullanın. Boş bırakırsanız isimden otomatik üretilir.</p>
                @error('slug')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-sm font-semibold text-slate-700">Açıklama</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Kategorinizi kısaca tanımlayın..."
                    class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 pt-2 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Profil görseli</label>
                    <label
                        data-category-upload="profile_image"
                        class="group relative flex flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-5 transition hover:border-blue-400 hover:bg-blue-50/30"
                        style="cursor: pointer;"
                    >
                        <input id="profile_image" type="file" name="profile_image" accept="image/png, image/jpeg, image/webp" class="hidden" data-category-upload-input>

                        <img data-category-upload-preview class="hidden h-16 w-16 rounded-full object-cover" alt="">

                        <svg data-category-upload-icon class="mb-2 h-8 w-8 text-slate-400 transition group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                        </svg>
                        <span data-category-upload-label class="text-center text-xs font-semibold text-slate-700 transition group-hover:text-blue-600">
                            Yüklemek için tıklayın veya sürükleyin
                        </span>
                        <span class="mt-1 text-[11px] text-slate-400">PNG, JPG, WEBP — max 10MB</span>
                    </label>
                    @error('profile_image')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-700">Kapak görseli</label>
                    <label
                        data-category-upload="cover_image"
                        class="group relative flex flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-5 transition hover:border-blue-400 hover:bg-blue-50/30"
                        style="cursor: pointer;"
                    >
                        <input id="cover_image" type="file" name="cover_image" accept="image/png, image/jpeg, image/webp" class="hidden" data-category-upload-input>

                        <img data-category-upload-preview class="hidden h-16 w-full rounded-lg object-cover" alt="">

                        <svg data-category-upload-icon class="mb-2 h-8 w-8 text-slate-400 transition group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span data-category-upload-label class="text-center text-xs font-semibold text-slate-700 transition group-hover:text-blue-600">
                            Yüklemek için tıklayın veya sürükleyin
                        </span>
                        <span class="mt-1 text-[11px] text-slate-400">PNG, JPG, WEBP — max 15MB</span>
                    </label>
                    @error('cover_image')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('blog.categories') }}" class="rounded-xl px-5 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                    İptal
                </a>
                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-200 transition hover:bg-blue-700 active:scale-95">
                    Kategoriyi oluştur
                </button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('[data-category-upload]').forEach(function (wrap) {
            const input = wrap.querySelector('[data-category-upload-input]');
            const preview = wrap.querySelector('[data-category-upload-preview]');
            const icon = wrap.querySelector('[data-category-upload-icon]');
            const label = wrap.querySelector('[data-category-upload-label]');

            if (!input || !preview) {
                return;
            }

            input.addEventListener('change', function () {
                const file = input.files && input.files[0];
                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.src = event.target.result;
                    preview.classList.remove('hidden');
                    icon?.classList.add('hidden');
                    if (label) {
                        label.textContent = file.name;
                    }
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
@endsection
