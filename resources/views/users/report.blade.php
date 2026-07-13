@extends('layouts.app')

@section('title', 'Sikayet Et')

@section('content')
    <div class="mx-auto w-full max-w-[45rem]">
        <div class="rounded-2xl p-6 shadow-sm">
            <div class="flex items-start gap-3 pb-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 2.82 18a1.5 1.5 0 0 0 1.31 2.2h15.74a1.5 1.5 0 0 0 1.31-2.2L13.71 3.86a1.5 1.5 0 0 0-2.62 0Z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Guvenlik</p>
                    <h1 class="text-xl font-bold text-slate-900">Sikayet Et</h1>
                    <p class="text-sm text-slate-600">Sikayet edecegin kisi: <span class="font-semibold text-slate-900">{{ $user->name }}</span></p>
                </div>
            </div>

            <form class="space-y-4 pt-4" action="{{ route('users.report', $user) }}" method="POST">
                @csrf
                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Kullanici adim gorunsun mu?</p>
                        <p class="text-xs text-slate-500">Sikayetin kullanici adinla paylasilir.</p>
                    </div>
                    <div class="shrink-0">
                        <input type="hidden" name="show_username" value="0">
                        <x-ui.switch
                            name="show_username"
                            value="1"
                            :checked="old('show_username', 1) == 1"
                        />
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-900 mb-2">Konu</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($topics as $topic)
                            <label class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-gray-100">
                                <input type="radio" name="topic" value="{{ $topic }}" class="h-4 w-4 text-gray-500" required>
                                <span>{{ $topic }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="reportMessage" class="text-sm font-semibold text-slate-900">Aciklama</label>
                    <textarea id="reportMessage" name="message" rows="3" class="mt-2 w-full rounded-xl px-3 py-2 text-sm text-slate-700 focus:outline-none" placeholder="Kisa bir aciklama ekle..."></textarea>
                </div>

                <label class="flex items-start gap-3 text-sm text-slate-700">
                    <input type="checkbox" name="terms" class="mt-1 h-4 w-4 rounded text-gray-500" required>
                    <span>Hukum ve sartlari kabul ediyorum.</span>
                </label>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('users.show', $user) }}" class="rounded-full px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-100">Vazgec</a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        Gonder
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection




