<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start gap-3">
            <div class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3a1.7 1.7 0 0 0-1 1.5V22a2 2 0 1 1-4 0v-.2a1.7 1.7 0 0 0-1-1.5a1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8a1.7 1.7 0 0 0-1.5-1H2a2 2 0 1 1 0-4h.2a1.7 1.7 0 0 0 1.5-1a1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H8a1.7 1.7 0 0 0 1-1.5V2a2 2 0 1 1 4 0v.2a1.7 1.7 0 0 0 1 1.5a1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V8c0 .7.4 1.3 1 1.5h.2a2 2 0 1 1 0 4h-.2a1.7 1.7 0 0 0-1.5 1Z"></path>
                </svg>
            </div>
            <div>
                <h2 class="font-semibold text-xl text-slate-900 leading-tight">Ayarlar</h2>
                <p class="mt-1 text-sm text-slate-500">Kullanici, bildirim ve mesaj tercihlerini yonet.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-[45rem] sm:px-6 lg:px-8">
            <div class="space-y-3">
                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z"></path>
                                    <path d="M4 20a8 8 0 0 1 16 0"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Profil gorunurlugu</p>
                                <p class="mt-1 text-xs text-slate-500">Profilini herkese acik tut.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3a1.7 1.7 0 0 0-1 1.5V22a2 2 0 1 1-4 0v-.2a1.7 1.7 0 0 0-1-1.5a1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8a1.7 1.7 0 0 0-1.5-1H2a2 2 0 1 1 0-4h.2a1.7 1.7 0 0 0 1.5-1a1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H8a1.7 1.7 0 0 0 1-1.5V2a2 2 0 1 1 4 0v.2a1.7 1.7 0 0 0 1 1.5a1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V8c0 .7.4 1.3 1 1.5h.2a2 2 0 1 1 0 4h-.2a1.7 1.7 0 0 0-1.5 1Z"></path>
                                        </svg>
                                    </span>
                                    Kullanici
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900">
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Iki adimli koruma</p>
                                <p class="mt-1 text-xs text-slate-500">Giris guvenligini artir.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z"></path>
                                            <path d="M4 20a8 8 0 0 1 16 0"></path>
                                        </svg>
                                    </span>
                                    Kullanici
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900">
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 4h16v16H4z"></path>
                                    <path d="M22 6l-10 7L2 6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Eposta bildirimleri</p>
                                <p class="mt-1 text-xs text-slate-500">Onemli guncellemeler.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M14 10a2 2 0 10-4 0c0 3.5-1.5 4.5-2 5h8c-.5-.5-2-1.5-2-5z"></path>
                                            <path d="M18 15H6"></path>
                                            <path d="M9.5 19a2.5 2.5 0 005 0"></path>
                                        </svg>
                                    </span>
                                    Bildirim
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900" checked>
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 2a7 7 0 0 1 7 7v3l2 3H3l2-3V9a7 7 0 0 1 7-7Z"></path>
                                    <path d="M8 21a4 4 0 0 0 8 0"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Push bildirimleri</p>
                                <p class="mt-1 text-xs text-slate-500">Anlik haberdar ol.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M14 10a2 2 0 10-4 0c0 3.5-1.5 4.5-2 5h8c-.5-.5-2-1.5-2-5z"></path>
                                            <path d="M18 15H6"></path>
                                            <path d="M9.5 19a2.5 2.5 0 005 0"></path>
                                        </svg>
                                    </span>
                                    Bildirim
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900">
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                    <path d="M16 2v4"></path>
                                    <path d="M8 2v4"></path>
                                    <path d="M3 10h18"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Haftalik ozet</p>
                                <p class="mt-1 text-xs text-slate-500">Haftalik rapor al.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M14 10a2 2 0 10-4 0c0 3.5-1.5 4.5-2 5h8c-.5-.5-2-1.5-2-5z"></path>
                                            <path d="M18 15H6"></path>
                                            <path d="M9.5 19a2.5 2.5 0 005 0"></path>
                                        </svg>
                                    </span>
                                    Bildirim
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900">
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h6"></path>
                                    <path d="M16 5h5v5"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Okundu bilgisi</p>
                                <p class="mt-1 text-xs text-slate-500">Okundu bildirimi goster.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 11.5a8.5 8.5 0 1 1-4.9-7.7"></path>
                                            <path d="M22 4 12 14l-3-3"></path>
                                        </svg>
                                    </span>
                                    Mesaj
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900" checked>
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20l9-8-9-8-9 8 9 8Z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Sohbet onizleme</p>
                                <p class="mt-1 text-xs text-slate-500">Mesaj onizlemesini goster.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 11.5a8.5 8.5 0 1 1-4.9-7.7"></path>
                                            <path d="M22 4 12 14l-3-3"></path>
                                        </svg>
                                    </span>
                                    Mesaj
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900">
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 12h6"></path>
                                    <path d="M14 12h6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Yaziyor bildirimi</p>
                                <p class="mt-1 text-xs text-slate-500">Yaziyor durumunu goster.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M21 11.5a8.5 8.5 0 1 1-4.9-7.7"></path>
                                            <path d="M22 4 12 14l-3-3"></path>
                                        </svg>
                                    </span>
                                    Mesaj
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900" checked>
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-purple-600 text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 3a9 9 0 1 0 9 9"></path>
                                    <path d="M12 7a5 5 0 1 0 5 5"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Otomatik tema</p>
                                <p class="mt-1 text-xs text-slate-500">Sisteme gore tema.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 1v6"></path>
                                            <path d="M12 17v6"></path>
                                            <path d="M4.2 4.2l4.2 4.2"></path>
                                            <path d="M15.6 15.6l4.2 4.2"></path>
                                            <path d="M1 12h6"></path>
                                            <path d="M17 12h6"></path>
                                            <path d="M4.2 19.8l4.2-4.2"></path>
                                            <path d="M15.6 8.4l4.2-4.2"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </span>
                                    Ayarlar
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900" checked>
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-purple-600 text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 4h16v16H4z"></path>
                                    <path d="M8 8h8"></path>
                                    <path d="M8 12h6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Otomatik kaydet</p>
                                <p class="mt-1 text-xs text-slate-500">Icerigi otomatik kaydet.</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 1v6"></path>
                                            <path d="M12 17v6"></path>
                                            <path d="M4.2 4.2l4.2 4.2"></path>
                                            <path d="M15.6 15.6l4.2 4.2"></path>
                                            <path d="M1 12h6"></path>
                                            <path d="M17 12h6"></path>
                                            <path d="M4.2 19.8l4.2-4.2"></path>
                                            <path d="M15.6 8.4l4.2-4.2"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </span>
                                    Ayarlar
                                </span>
                            </div>
                        </div>
                        <input type="checkbox" class="mt-1 h-5 w-5 rounded text-slate-900">
                    </div>
                </div>

                <div class="rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-purple-600 text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="M2.5 12h19"></path>
                                    <path d="M12 2.5v19"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Dil</p>
                                <p class="mt-1 text-xs text-slate-500">Turkce (TR)</p>
                                <span class="mt-2 inline-flex items-center gap-2 text-xs text-slate-500">
                                    <span class="text-slate-400">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 1v6"></path>
                                            <path d="M12 17v6"></path>
                                            <path d="M4.2 4.2l4.2 4.2"></path>
                                            <path d="M15.6 15.6l4.2 4.2"></path>
                                            <path d="M1 12h6"></path>
                                            <path d="M17 12h6"></path>
                                            <path d="M4.2 19.8l4.2-4.2"></path>
                                            <path d="M15.6 8.4l4.2-4.2"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </span>
                                    Ayarlar
                                </span>
                            </div>
                        </div>
                        <button type="button" class="mt-1 inline-flex items-center gap-2 rounded-lg px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            <span class="text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 5v14"></path>
                                    <path d="M5 12h14"></path>
                                </svg>
                            </span>
                            <span>Degistir</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5"></path>
                    </svg>
                    <span>Kaydet</span>
                </button>
            </div>
        </div>
    </div>
</x-app-layout>


