<x-install-layout>
    <h2 class="text-xl font-semibold">Yonetici Hesabi</h2>
    <p class="mt-1 text-sm text-slate-400">Ilk kullanici hesabini olusturun.</p>

    @if ($errors->any())
        <div class="mt-4 rounded-lg bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="post" action="{{ route('install.admin.save') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="install-admin-name" class="text-sm text-slate-300">Ad</label>
            <input id="install-admin-name" name="name" value="{{ old('name') }}" autocomplete="name" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white" required>
        </div>
        <div>
            <label for="install-admin-email" class="text-sm text-slate-300">E-posta</label>
            <input id="install-admin-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white" required>
        </div>
        <div>
            <label for="install-admin-password" class="text-sm text-slate-300">Sifre</label>
            <input id="install-admin-password" name="password" type="password" autocomplete="new-password" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white" required>
        </div>
        <div>
            <label for="install-admin-password-confirmation" class="text-sm text-slate-300">Sifre Tekrari</label>
            <input id="install-admin-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white" required>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('install.database') }}" class="text-sm text-slate-400 hover:text-slate-200">Geri</a>
            <button class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">
                Kurulumu Tamamla
            </button>
        </div>
    </form>
</x-install-layout>

