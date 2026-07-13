<x-install-layout>
    <h2 class="text-xl font-semibold">Veritabani</h2>
    <p class="mt-1 text-sm text-slate-400">Veritabani baglanti bilgilerini girin.</p>

    @if ($errors->any())
        <div class="mt-4 rounded-lg bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="post" action="{{ route('install.database.save') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="install-db-host" class="text-sm text-slate-300">Sunucu</label>
            <input id="install-db-host" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" autocomplete="off" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white" required>
        </div>
        <div>
            <label for="install-db-port" class="text-sm text-slate-300">Port</label>
            <input id="install-db-port" name="db_port" type="number" value="{{ old('db_port', 3306) }}" inputmode="numeric" autocomplete="off" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white" required>
        </div>
        <div>
            <label for="install-db-name" class="text-sm text-slate-300">Veritabani</label>
            <input id="install-db-name" name="db_name" value="{{ old('db_name') }}" autocomplete="off" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white" required>
        </div>
        <div>
            <label for="install-db-user" class="text-sm text-slate-300">Kullanici Adi</label>
            <input id="install-db-user" name="db_user" value="{{ old('db_user') }}" autocomplete="username" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white" required>
        </div>
        <div>
            <label for="install-db-pass" class="text-sm text-slate-300">Sifre</label>
            <input id="install-db-pass" name="db_pass" type="password" value="{{ old('db_pass') }}" autocomplete="current-password" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white">
        </div>
        <div>
            <label for="install-app-url" class="text-sm text-slate-300">Uygulama URL'si (opsiyonel)</label>
            <input id="install-app-url" name="app_url" value="{{ old('app_url', config('app.url')) }}" autocomplete="url" class="mt-1 w-full rounded-lg bg-slate-950 px-3 py-2 text-sm text-white">
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('install.requirements') }}" class="text-sm text-slate-400 hover:text-slate-200">Geri</a>
            <button class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400">
                Kaydet ve Devam Et
            </button>
        </div>
    </form>
</x-install-layout>

