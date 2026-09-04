@php
    $rankbeamMeta = null;
    if (isset($post) && $post) {
        try {
            $rankbeamMeta = $post->seoMetaForLocale(app()->getLocale())->first();
        } catch (\Throwable $e) {
            $rankbeamMeta = null;
        }
    }

    $rankbeamKeywordValue = old('rankbeam_focus_keywords');
    if ($rankbeamKeywordValue === null) {
        $rankbeamKeywordValue = collect($rankbeamMeta?->focus_keywords ?? [])
            ->map(fn ($item) => is_array($item) ? ($item['keyword'] ?? null) : $item)
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->implode(', ');

        if ($rankbeamKeywordValue === '' && isset($post)) {
            $rankbeamKeywordValue = (string) ($post->meta_keywords ?? '');
        }
    }

    $rankbeamTitleValue = old('rankbeam_title', $rankbeamMeta?->title ?? (isset($post) ? $post->meta_title : null));
    $rankbeamDescriptionValue = old('rankbeam_description', $rankbeamMeta?->description ?? (isset($post) ? $post->meta_description : null));
    $rankbeamCanonicalValue = old('rankbeam_canonical', $rankbeamMeta?->canonical);
    $rankbeamRobotsValue = old('rankbeam_robots', $rankbeamMeta?->robots ?? ((isset($post) && $post->noindex) ? 'noindex, follow' : ''));
    $rankbeamOgImageValue = old('rankbeam_og_image', $rankbeamMeta?->og_image);
    $rankbeamInputClass = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100';
@endphp

<div class="mt-4 border-t border-slate-100 pt-4" data-rankbeam-seo-fields>
    <div class="mb-3 flex items-start justify-between gap-3">
        <div>
            <div class="text-sm font-semibold text-slate-950">Rankbeam SEO</div>
            <p class="mt-0.5 text-xs leading-5 text-slate-500">Filament ile ayni SEO kaydini kullanir. Bos alanlar yazinin mevcut SEO bilgilerinden otomatik tamamlanir.</p>
        </div>
        <span class="shrink-0 rounded-full border border-blue-100 bg-blue-50 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-blue-700">SEO</span>
    </div>

    <div class="space-y-3">
        <div>
            <label for="rankbeam_title" class="mb-1 block text-xs font-semibold text-slate-700">SEO basligi</label>
            <input id="rankbeam_title" name="rankbeam_title" type="text" maxlength="255" value="{{ $rankbeamTitleValue }}" placeholder="Arama sonucundaki baslik" class="{{ $rankbeamInputClass }}">
        </div>

        <div>
            <label for="rankbeam_description" class="mb-1 block text-xs font-semibold text-slate-700">SEO aciklamasi</label>
            <textarea id="rankbeam_description" name="rankbeam_description" rows="3" maxlength="500" placeholder="Arama sonucundaki aciklama" class="{{ $rankbeamInputClass }} resize-none">{{ $rankbeamDescriptionValue }}</textarea>
        </div>

        <div>
            <label for="rankbeam_focus_keywords" class="mb-1 block text-xs font-semibold text-slate-700">Odak anahtar kelimeler</label>
            <input id="rankbeam_focus_keywords" name="rankbeam_focus_keywords" type="text" maxlength="1000" value="{{ $rankbeamKeywordValue }}" placeholder="laravel seo, web tasarim, teknoloji" class="{{ $rankbeamInputClass }}">
            <p class="mt-1 text-[11px] text-slate-400">Virgulle ayirin. Ilk kelime birincil odak kelime olur.</p>
        </div>

        <div>
            <label for="rankbeam_canonical" class="mb-1 block text-xs font-semibold text-slate-700">Canonical URL</label>
            <input id="rankbeam_canonical" name="rankbeam_canonical" type="url" maxlength="2048" value="{{ $rankbeamCanonicalValue }}" placeholder="https://ografi.com/..." class="{{ $rankbeamInputClass }}">
        </div>

        <div>
            <label for="rankbeam_robots" class="mb-1 block text-xs font-semibold text-slate-700">Robots</label>
            <select id="rankbeam_robots" name="rankbeam_robots" class="{{ $rankbeamInputClass }}">
                <option value="" @selected($rankbeamRobotsValue === '')>Otomatik</option>
                <option value="index, follow" @selected($rankbeamRobotsValue === 'index, follow')>Index, linkleri takip et</option>
                <option value="index, nofollow" @selected($rankbeamRobotsValue === 'index, nofollow')>Index, linkleri takip etme</option>
                <option value="noindex, follow" @selected($rankbeamRobotsValue === 'noindex, follow')>Noindex, linkleri takip et</option>
                <option value="noindex, nofollow" @selected($rankbeamRobotsValue === 'noindex, nofollow')>Noindex, linkleri takip etme</option>
            </select>
        </div>

        <div>
            <label for="rankbeam_og_image" class="mb-1 block text-xs font-semibold text-slate-700">Sosyal paylasim gorseli</label>
            <input id="rankbeam_og_image" name="rankbeam_og_image" type="text" maxlength="2048" value="{{ $rankbeamOgImageValue }}" placeholder="https://... veya seo/gorsel.webp" class="{{ $rankbeamInputClass }}">
            <p class="mt-1 text-[11px] text-slate-400">Bos birakilirsa yazinin mevcut paylasim/one cikan gorseli kullanilir.</p>
        </div>
    </div>
</div>
