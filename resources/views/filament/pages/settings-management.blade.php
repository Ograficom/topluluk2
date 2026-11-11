<x-filament-panels::page>
    <style>
        .settings-landing {
            position:relative;
            isolation:isolate;
            display:flex;
            align-items:center;
            gap:20px;
            overflow:hidden;
            padding:26px;
            border:1px solid #dbe7fb;
            border-radius:18px;
            background:
                radial-gradient(circle at 94% 8%, rgba(99,102,241,.16), transparent 27%),
                radial-gradient(circle at 4% 100%, rgba(14,165,233,.13), transparent 32%),
                linear-gradient(115deg,#f0f7ff 0%,#f8faff 48%,#f3f1ff 100%);
        }
        .settings-landing::after {
            content:"";
            position:absolute;
            z-index:-1;
            width:210px;
            height:210px;
            right:-78px;
            bottom:-145px;
            border:28px solid rgba(59,130,246,.10);
            border-radius:50%;
        }
        .settings-landing-icon {
            display:grid;
            place-items:center;
            flex:0 0 58px;
            width:58px;
            height:58px;
            border:1px solid rgba(255,255,255,.9);
            border-radius:17px;
            background:linear-gradient(145deg,#2563eb,#6366f1);
            color:#fff;
            box-shadow:0 8px 18px rgba(37,99,235,.20);
        }
        .settings-landing-icon svg { width:27px;height:27px; }
        .settings-landing-content { min-width:0; }
        .settings-landing-eyebrow {
            margin:0 0 5px;
            color:#2563eb;
            font-size:10px;
            font-weight:800;
            letter-spacing:.12em;
            text-transform:uppercase;
        }
        .settings-landing h2 {
            margin:0;
            color:#172554;
            font-size:18px;
            font-weight:800;
            letter-spacing:-.025em;
        }
        .settings-landing p {
            margin:6px 0 0;
            color:#64748b;
            font-size:13px;
            line-height:1.7;
        }
        .settings-landing-tags {
            display:flex;
            flex-wrap:wrap;
            gap:7px;
            margin-top:14px;
        }
        .settings-landing-tag {
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:6px 9px;
            border:1px solid rgba(148,163,184,.24);
            border-radius:8px;
            background:rgba(255,255,255,.72);
            color:#475569;
            font-size:11px;
            font-weight:650;
        }
        .settings-landing-tag svg { width:14px;height:14px;color:#4f73d9; }
        .dark .settings-landing {
            border-color:#34445e;
            background:
                radial-gradient(circle at 94% 8%, rgba(99,102,241,.20), transparent 27%),
                radial-gradient(circle at 4% 100%, rgba(14,165,233,.13), transparent 32%),
                linear-gradient(115deg,#17243a 0%,#1b2638 48%,#25213d 100%);
        }
        .dark .settings-landing h2 { color:#f1f5f9; }
        .dark .settings-landing p { color:#a8b6ca; }
        .dark .settings-landing-eyebrow { color:#93c5fd; }
        .dark .settings-landing-tag {
            border-color:#3c4b61;
            background:rgba(15,23,42,.48);
            color:#cbd5e1;
        }
        .dark .settings-landing-tag svg { color:#93c5fd; }
        @media(max-width:640px) {
            .settings-landing { align-items:flex-start; gap:14px; padding:18px; }
            .settings-landing-icon { flex-basis:48px;width:48px;height:48px;border-radius:14px; }
            .settings-landing h2 { font-size:16px; }
        }
    </style>

    <section class="settings-landing">
        <div class="settings-landing-icon">
            <x-filament::icon icon="heroicon-o-adjustments-horizontal" />
        </div>
        <div class="settings-landing-content">
            <p class="settings-landing-eyebrow">OGRAFI YÖNETİM MERKEZİ</p>
            <h2>Site ayarları</h2>
            <p>Marka kimliğini, e-posta altyapısını, SEO görünürlüğünü, sitemap ve RSS yapılandırmalarını tek merkezden yönet.</p>
            <div class="settings-landing-tags">
                <span class="settings-landing-tag"><x-filament::icon icon="heroicon-o-swatch" /> Marka</span>
                <span class="settings-landing-tag"><x-filament::icon icon="heroicon-o-envelope" /> E-posta</span>
                <span class="settings-landing-tag"><x-filament::icon icon="heroicon-o-magnifying-glass" /> SEO</span>
                <span class="settings-landing-tag"><x-filament::icon icon="heroicon-o-globe-alt" /> Sitemap</span>
                <span class="settings-landing-tag"><x-filament::icon icon="heroicon-o-rss" /> RSS</span>
            </div>
        </div>
    </section>
</x-filament-panels::page>