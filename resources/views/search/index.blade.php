@extends('layouts.app')

@section('title', __('site.search.title'))
@section('meta_description', __('site.search.meta_description'))

<style>
    .og-search-page {
        display: flex;
        flex-direction: column;
        gap: 14px;
        width: 100%;
        min-width: 0;
    }

    .og-search-bar-row {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
    }

    /* Geri butonu artik arama kutusunun yaninda degil, asagidaki kimlik
       kutusunun (.og-search-identity) icinde - Etiketler/SSS sayfalarindaki
       "< | Baslik" desenoyla ayni yerlesim. */
    .og-search-identity__back,
    body.alma-app .og-search-identity__back {
        flex: 0 0 auto;
        display: none;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 9999px;
        border: 0 !important;
        background: transparent !important;
        color: #334155 !important;
        font-size: 15px;
        cursor: pointer;
    }

    .og-search-identity__back:hover,
    body.alma-app .og-search-identity__back:hover {
        background: #f1f5f9 !important;
    }

    .og-search-identity__divider {
        display: none;
        width: 1px;
        height: 16px;
        flex: 0 0 auto;
        background: currentColor;
        opacity: .15;
    }

    .og-search-bar {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        height: 48px;
        padding: 0 46px 0 16px;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        background: #ffffff !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .og-search-bar:focus-within {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12) !important;
    }

    /* Arama ve temizle (x) ikonlari ayni sag ust bosluga (ayni "slot") yerlesir;
       JS (syncPillStates) ikisinden sadece birini gosterir, asla ikisi birden
       gorunmez - boylece ikonlar ust uste binmez. */
    .og-search-bar-icon,
    .og-search-bar-clear {
        position: absolute !important;
        right: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
    }

    /* Site genelinde iconify-icon etiketlerine "transform: none !important"
       uygulayan bir sifirlama var (bkz. .site-icon-btn * ... , iconify-icon
       kurali); ust satirdaki !important olmadan bu, yukaridaki dikey ortalama
       translateY'sini eziyor ve ikonun kutunun alt kenarina dogru kaymasina
       yol aciyordu. */
    .og-search-bar-icon {
        display: inline-flex !important;
        width: 26px;
        height: 26px;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #94a3b8;
        pointer-events: none;
    }

    .og-search-bar-icon.hidden {
        display: none !important;
    }

    .og-search-bar-input {
        width: 100%;
        height: 100%;
        border: 0;
        background: transparent !important;
        font-size: 15px;
        color: #0f172a;
        outline: none;
    }

    /*
    HOTFIX: sitede body.alma-app :where(input, textarea, select):not(#comments *)
    seklinde genel bir form-input sifirlama kurali var; :not(#comments *) icindeki
    #comments ID secicisi bu kurala yanlislikla ID-seviyesi ozgullugu kazandiriyor
    (bkz. .og-search-bar/.og-search-bar-input arka planinin gri kalmasi sorunu).
    Bunu asmak icin input'a ID verip ayni/daha yuksek ozgullukte eziyoruz.
    */
    html body #og-search-page-input.og-search-bar-input {
        min-height: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: #0f172a !important;
        box-shadow: none !important;
    }

    .og-search-bar-input::placeholder {
        color: #94a3b8;
    }

    /* Tarayicinin otomatik doldurma (autofill) on izleme rengi olmasa bile
       bazi tarayicilarda input'a varsayilan gri bir kutu arka plani
       uygulayabiliyor; input'un beyaz kalmasini garanti eder. */
    .og-search-bar-input:-webkit-autofill,
    .og-search-bar-input:-webkit-autofill:hover,
    .og-search-bar-input:-webkit-autofill:focus {
        -webkit-text-fill-color: #0f172a !important;
        -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
        box-shadow: 0 0 0 1000px #ffffff inset !important;
        background-color: #ffffff !important;
    }

    .og-search-bar-clear {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 9999px;
        border: 0 !important;
        background: #f1f5f9 !important;
        color: #64748b !important;
    }

    .og-search-bar-clear:hover {
        background: #e2e8f0 !important;
    }

    .og-search-bar-clear:active {
        background: #cbd5e1 !important;
    }

    /*
    Once bu kimlik kutusu (geri + "Arama" basligi + ayarlar tetikleyicisi)
    sadece <640px'te goruniyordu; masaustunde tum siralama/filtre/tur
    pilleri acik satirlar halinde goruntyordu. Artik Etiketler/SSS/
    Kullanicilar sayfalarindaki .page-title-identity ile AYNI davranisi
    her genislikte gosteriyor: kutu her zaman gorunur, tetikleyici (ayarlar
    dislisi ikonu - filtre huni ikonu DEGIL, cunku burada sirala+filtrele+
    tur olmak uzere BIRDEN FAZLA ayar grubu var) her zaman tikla-ac/kapa
    acilir menuyu kontrol eder. Masaustunde kutu normal (kenarlikli,
    yuvarlak) duruyor; sadece <640px'te Etiketler/SSS'deki gibi ekran
    kenarina kadar tam-genislik tasiyor (asagidaki media query). */
    .og-search-identity {
        display: flex;
        align-items: center;
        gap: 6px;
        width: 100%;
        min-height: 38px;
        padding: 3px 12px;
        border: 1px solid #d9dde3;
        border-radius: 18px;
        background: #ffffff;
    }

    .og-search-identity__back {
        display: inline-flex !important;
        transition: background-color .15s ease, transform .08s ease-out;
    }

    .og-search-identity__back:active {
        transform: translateY(1px);
    }

    .og-search-identity__divider {
        display: block;
    }

    .og-search-identity__title {
        display: block;
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
    }

    .og-search-identity__actions {
        display: flex;
        align-items: center;
        gap: 2px;
        flex: 0 0 auto;
        margin-left: auto;
    }

    .og-search-settings {
        position: relative;
        display: flex;
        align-items: center;
        flex: 0 0 auto;
        width: auto;
    }

    /* Bilgi (i) tetikleyicisi - ayarlar dislisiyle ayni kalip: cift class
       sitedeki genel buton sifirlamasindan (body.alma-app :where(button...))
       ozgullukce ustun gelmek icin. */
    .og-search-info {
        position: relative;
        display: flex;
        align-items: center;
    }

    .og-search-info__trigger.og-search-info__trigger {
        display: inline-flex !important;
        width: 32px !important;
        height: 32px !important;
        align-items: center;
        justify-content: center;
        border: 0 !important;
        border-radius: 999px !important;
        background: transparent !important;
        color: #52525b !important;
        cursor: pointer;
        transition: background-color .15s ease, transform .08s ease-out;
    }

    .og-search-info__trigger:active {
        transform: translateY(1px);
    }

    .og-search-info__trigger iconify-icon {
        font-size: 16px;
    }

    .og-search-info__trigger.og-search-info__trigger:hover,
    .og-search-info__trigger.og-search-info__trigger:focus-visible,
    .og-search-info.is-open .og-search-info__trigger.og-search-info__trigger {
        background: #f3f4f6 !important;
    }

    /* Ayni "materialize, don't just fade" davranisi: tetikleyicinin sag
       ustunden kucuk bir olcek+kayma ile belirir, kapanista visibility
       gecisi opaklik/olcek bitene kadar geciktirilir. */
    .og-search-info-popover {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: min(280px, calc(100vw - 32px));
        padding: 18px 18px 16px;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .14);
        z-index: 41;
        text-align: center;
        visibility: hidden;
        opacity: 0;
        transform: scale(.96) translateY(-6px);
        transform-origin: top right;
        transition: opacity .16s ease, transform .16s ease, visibility 0s linear .16s;
    }

    .og-search-info-popover.is-open {
        visibility: visible;
        opacity: 1;
        transform: scale(1) translateY(0);
        transition: opacity .16s ease, transform .16s ease;
    }

    @media (prefers-reduced-motion: reduce) {
        .og-search-info-popover {
            transform: none;
            transition: opacity .12s ease, visibility 0s linear .12s;
        }

        .og-search-info-popover.is-open {
            transform: none;
            transition: opacity .12s ease;
        }
    }

    .og-search-info-popover__close.og-search-info-popover__close {
        position: absolute;
        top: 10px;
        right: 10px;
        display: inline-flex !important;
        width: 26px !important;
        height: 26px !important;
        align-items: center;
        justify-content: center;
        border: 0 !important;
        border-radius: 999px !important;
        background: transparent !important;
        color: #94a3b8 !important;
        cursor: pointer;
        font-size: 13px;
        transition: background-color .15s ease, transform .08s ease-out;
    }

    .og-search-info-popover__close:active {
        transform: translateY(1px);
    }

    .og-search-info-popover__close.og-search-info-popover__close:hover,
    .og-search-info-popover__close.og-search-info-popover__close:focus-visible {
        background: #f1f5f9 !important;
        color: #475569 !important;
    }

    .og-search-info-popover__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        margin: 4px auto 10px;
        border-radius: 999px;
        background: linear-gradient(160deg, #dbeafe, #eff6ff);
        color: #2563eb;
        font-size: 18px;
    }

    .og-search-info-popover__title {
        margin: 0 0 6px;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    .og-search-info-popover__text {
        margin: 0 0 14px;
        font-size: 12.5px;
        line-height: 1.55;
        color: #64748b;
    }

    .og-search-info-popover__ok.og-search-info-popover__ok {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 36px;
        border: 0 !important;
        border-radius: 12px !important;
        background: #2563eb !important;
        color: #ffffff !important;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color .15s ease, transform .08s ease-out;
    }

    .og-search-info-popover__ok:active {
        transform: translateY(1px);
    }

    .og-search-info-popover__ok.og-search-info-popover__ok:hover,
    .og-search-info-popover__ok.og-search-info-popover__ok:focus-visible {
        background: #1d4ed8 !important;
    }

    /* .og-search-settings__trigger iki kez yazildi: site genelindeki
       "body.alma-app :where(button...) {background:#fff !important}"
       resetiyle ayni ozgulluk yarisi (bkz. Kullanicilar/Discover sayfalari) -
       tek class + !important o kuraldan (body+class) daha dusuk ozgullukte
       kalip beyaza eziliyordu, ikon koyu modda beyaz daire gibi duruyordu. */
    .og-search-settings__trigger.og-search-settings__trigger {
        display: inline-flex !important;
        width: 32px !important;
        height: 32px !important;
        align-items: center;
        justify-content: center;
        border: 0 !important;
        border-radius: 999px !important;
        background: transparent !important;
        color: #52525b !important;
        cursor: pointer;
        transition: background-color .15s ease, transform .08s ease-out;
    }

    .og-search-settings__trigger:active {
        transform: translateY(1px);
    }

    .og-search-settings__trigger iconify-icon {
        font-size: 17px;
    }

    .og-search-settings__trigger.og-search-settings__trigger:hover,
    .og-search-settings__trigger.og-search-settings__trigger:focus-visible,
    .og-search-settings.is-open .og-search-settings__trigger.og-search-settings__trigger {
        background: #f3f4f6 !important;
    }

    /* Acilir menu - JS'in ekleyip cikardigi ".is-open" sinifiyla kontrol
       edilir; native "hidden" ozniteligi KASITLI olarak kullanilmadi:
       Chromium'da bazi durumlarda [hidden] elemente CSS ile display
       verilse bile layout hesaba katmiyor (olcum: display:flex computed
       style'da gorunuyor ama getBoundingClientRect() hep 0x0 donuyordu).
       display:none yerine gorunmezligi visibility+opacity+transform ile
       kontrol ediyoruz - boylece Materials ilkesindeki "materialize, don't
       just fade" tavsiyesine uyup tetikleyiciden (sag ust) dogru kucuk bir
       olcek+kayma ile "belirir", duz bir acilma/kapanma yerine. Kapanista
       visibility gecisi opaklik/olcek animasyonu bitene kadar geciktiriliyor
       (0s linear .16s delay) - boylece erisilebilirlik agacindan/tiklama
       hedefinden duzgunce kalkarken de gorsel olarak aniden kesilmiyor. */
    .og-search-settings-menu {
        display: flex;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        width: min(272px, calc(100vw - 32px));
        max-height: 70vh;
        overflow-y: auto;
        padding: 14px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .14);
        z-index: 40;
        flex-direction: column;
        gap: 14px;
        visibility: hidden;
        opacity: 0;
        transform: scale(.96) translateY(-6px);
        transform-origin: top right;
        transition: opacity .16s ease, transform .16s ease, visibility 0s linear .16s;
    }

    .og-search-settings-menu.is-open {
        visibility: visible;
        opacity: 1;
        transform: scale(1) translateY(0);
        transition: opacity .16s ease, transform .16s ease;
    }

    @media (prefers-reduced-motion: reduce) {
        .og-search-settings-menu {
            transform: none;
            transition: opacity .12s ease, visibility 0s linear .12s;
        }

        .og-search-settings-menu.is-open {
            transform: none;
            transition: opacity .12s ease;
        }
    }

    .og-search-settings-menu__label {
        display: block;
        margin: 0 0 6px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .og-search-filters {
        flex-direction: column !important;
        align-items: stretch !important;
        justify-content: flex-start !important;
        gap: 12px !important;
    }

    .og-search-filters .og-search-pills {
        flex-direction: column;
        align-items: stretch;
        flex-wrap: nowrap;
        gap: 4px;
    }

    .og-search-filters .og-search-pill {
        width: 100%;
        justify-content: flex-start;
        height: 32px;
        padding: 0 10px;
    }

    .og-search-types {
        flex-wrap: wrap;
        overflow-x: visible;
    }

    .og-search-types .og-search-type-pill {
        flex: 1 1 calc(50% - 4px);
        justify-content: flex-start;
    }

    @media (max-width: 640px) {
        /* Etiketler/SSS/Kullanicilar sayfalarindaki .page-title-identity ile
           ayni "tam genislik" deseni: sayfanin kendi 12px yan bosluguna
           (.og-search-page padding-left/right) ragmen ekran kenarina kadar
           uzansin diye 100vw + negatif kenar bosluguyla disari tasiyor. */
        .og-search-identity {
            min-height: 40px;
            width: 100vw;
            margin-left: calc(50% - 50vw);
            margin-right: calc(50% - 50vw);
            padding: 3px 12px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            border-left: 0;
            border-right: 0;
            border-radius: 16px;
            background: #ffffff;
        }
    }

    html.dark .og-search-identity {
        background: #0f172a !important;
        border-color: #1e293b !important;
    }

    html.dark .og-search-identity__title {
        color: #e2e8f0 !important;
    }

    html.dark .og-search-identity__back {
        color: #cbd5e1 !important;
    }

    html.dark .og-search-identity__back:hover {
        background: #1e293b !important;
    }

    html.dark .og-search-settings__trigger {
        color: #cbd5e1 !important;
    }

    html.dark .og-search-settings__trigger:hover,
    html.dark .og-search-settings__trigger:focus-visible,
    html.dark .og-search-settings.is-open .og-search-settings__trigger {
        background: #1e293b !important;
    }

    html.dark .og-search-settings-menu {
        background: #0f172a !important;
        border-color: #1e293b !important;
    }

    html.dark .og-search-settings-menu__label {
        color: #64748b !important;
    }

    html.dark .og-search-info__trigger {
        color: #cbd5e1 !important;
    }

    html.dark .og-search-info__trigger:hover,
    html.dark .og-search-info__trigger:focus-visible,
    html.dark .og-search-info.is-open .og-search-info__trigger {
        background: #1e293b !important;
    }

    html.dark .og-search-info-popover {
        background: #0f172a !important;
        border-color: #1e293b !important;
    }

    html.dark .og-search-info-popover__title {
        color: #e2e8f0 !important;
    }

    html.dark .og-search-info-popover__text {
        color: #94a3b8 !important;
    }

    html.dark .og-search-info-popover__close {
        color: #64748b !important;
    }

    html.dark .og-search-info-popover__close:hover,
    html.dark .og-search-info-popover__close:focus-visible {
        background: #1e293b !important;
        color: #cbd5e1 !important;
    }

    html.dark .og-search-info-popover__icon {
        background: linear-gradient(160deg, #1e3a8a, #172554);
        color: #93c5fd;
    }

    .og-search-filters {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .og-search-pills {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .og-search-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        height: 30px;
        padding: 0 12px;
        border-radius: 9999px;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .002em;
        white-space: nowrap;
        transition: background-color 120ms ease, color 120ms ease, border-color 120ms ease, transform 80ms ease-out;
    }

    .og-search-pill iconify-icon {
        flex-shrink: 0;
        font-size: 13px;
        color: #64748b;
        transition: color 120ms ease;
    }

    .og-search-pill:hover {
        background: #f1f5f9 !important;
    }

    .og-search-pill:active {
        background: #e2e8f0 !important;
        transform: scale(.97);
    }

    .og-search-pill.is-active {
        background: #eff6ff !important;
        border-color: #bfdbfe !important;
        color: #1d4ed8 !important;
    }

    .og-search-pill.is-active iconify-icon {
        color: #2563eb;
    }

    /* Masaustunde (>640px) icerik-turu pilleri once yatay kaydirilan tek
       satirdaydi (overflow-x:auto) - sag kenardaki "Yorumlar" pili yarim
       kesilip goruniyor, kaydirilabilir oldugunu belli eden hicbir ipucu
       (fade/ok) yoktu, boylece bozuk/eksik gorunuyordu (Craft ilkesi
       ihlali - "hicbir sey rastgele/kazayla gorunmemeli"). .og-search-
       filters (siralama/NSFW-AI pilleri) zaten flex-wrap kullaniyordu;
       ayni tutarli davranisi burada da uygulayip masaustunde tum pilleri
       ikinci satira sararak gosteriyoruz - hicbir seyin kesilmis/gizli
       kalmasina gerek yok, mobilde zaten ayri bir wrap kurali var. */
    .og-search-types {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        width: 100%;
        min-width: 0;
    }

    .og-search-type-pill {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        height: 28px;
        padding: 0 10px;
        border-radius: 9999px;
        border: 1px solid #e2e8f0 !important;
        background: #f8fafc !important;
        color: #0f172a !important;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
        transition: background-color 120ms ease, color 120ms ease, border-color 120ms ease, transform 80ms ease-out;
    }

    .og-search-type-pill iconify-icon {
        flex-shrink: 0;
        font-size: 13px;
        color: #64748b;
        transition: color 120ms ease;
    }

    .og-search-type-pill:hover {
        background: #f1f5f9 !important;
    }

    .og-search-type-pill:active {
        background: #e2e8f0 !important;
        transform: scale(.97);
    }

    .og-search-type-pill.is-active {
        background: #eff6ff !important;
        border-color: #bfdbfe !important;
        color: #1d4ed8 !important;
    }

    .og-search-type-pill.is-active iconify-icon {
        color: #2563eb;
    }

    .og-search-follow-btn,
    body.alma-app .og-search-follow-btn {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 32px;
        padding: 0 16px;
        border-radius: 9999px;
        border: 0 !important;
        background: #2563eb !important;
        color: #ffffff !important;
        font-size: 12.5px;
        font-weight: 700;
        white-space: nowrap;
        transition: background-color 120ms ease;
    }

    .og-search-follow-btn:hover,
    body.alma-app .og-search-follow-btn:hover {
        background: #1d4ed8 !important;
    }

    .og-search-follow-btn.is-following,
    body.alma-app .og-search-follow-btn.is-following {
        background: #f1f5f9 !important;
        color: #334155 !important;
        border: 1px solid #e2e8f0 !important;
    }

    .og-search-follow-btn.is-following:hover,
    body.alma-app .og-search-follow-btn.is-following:hover {
        background: #fef2f2 !important;
        color: #b91c1c !important;
        border-color: #fecaca !important;
    }

    .og-search-results {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .og-search-message {
        padding: 28px 16px;
        text-align: center;
        font-size: 14px;
        color: #64748b;
    }

    .og-search-box {
        border-radius: var(--card-radius, 16px);
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 16px;
    }

    .og-search-box-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .og-search-box-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }

    .og-search-box-count {
        font-size: 12px;
        color: #94a3b8;
    }

    .og-search-box-more {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 10px;
        padding: 4px 2px;
        border: 0 !important;
        background: transparent !important;
        color: #2563eb !important;
        font-size: 13px;
        font-weight: 600;
    }

    .og-search-box-more iconify-icon {
        font-size: 14px;
        color: #2563eb;
    }

    .og-search-box-more:hover {
        color: #1d4ed8 !important;
        text-decoration: underline;
    }

    .og-search-box-more:active {
        color: #1e40af !important;
    }

    .og-result-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .og-result-flow {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .og-result-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 12px;
        text-decoration: none;
    }

    .og-result-row:hover {
        background: #f8fafc;
    }

    .og-result-row--post,
    .og-result-row--comment,
    .og-result-row--page {
        align-items: flex-start;
    }

    .og-result-row:active {
        background: #f1f5f9;
    }

    .og-result-row-main {
        min-width: 0;
        flex: 1 1 auto;
    }

    .og-result-row-title {
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Kategori/kullanici satirlarinda (chip) baslik gerisindeki rozet ve
       ok her zaman sag kenara yaslanmali - baslik esnek alani doldurup
       tasarsa tek satirda uc uca kesiliyor, digger satirlarin (post/
       sayfa) coklu-satir davranisini etkilemiyor. */
    .og-result-row--chip .og-result-row-title {
        flex: 1 1 auto;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .og-result-row-snippet {
        margin-top: 3px;
        font-size: 12.5px;
        line-height: 1.45;
        color: #64748b;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .og-result-row-meta {
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 11.5px;
        color: #94a3b8;
    }

    .og-result-avatar {
        flex: 0 0 auto;
        width: 36px;
        height: 36px;
    }

    .og-result-row-user {
        min-width: 0;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
    }

    .og-result-row-subtitle {
        font-size: 12px;
        color: #94a3b8;
    }

    .og-result-row-badge {
        flex: 0 0 auto;
        font-size: 11.5px;
        color: #94a3b8;
    }

    /* Yazi sonuclarindaki kare on-izleme kucuk resmi (iOS Notlar/Mail arama
       sonuclarindaki kucuk resim-sag desenoyle ayni dil) - resim yoksa
       nazikce bir yer-tutucu ikon gosterilir, bos beyaz kutu birakilmaz. */
    .og-result-row-thumb {
        flex: 0 0 auto;
        width: 56px;
        height: 56px;
        border-radius: 12px;
        object-fit: cover;
        background: #f1f5f9;
    }

    .og-result-row-thumb--placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #cbd5e1;
        font-size: 20px;
    }

    /* Kategoriler/Sayfalar icin iOS Ayarlar uygulamasindaki renkli-kare
       ikon rozeti deseni - her sonuc turu kendi rengini tasir, boylece
       liste tarayan goz turleri aninda ayirt edebilir (Grouping & mapping
       ilkesi). */
    .og-result-icon {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        font-size: 17px;
        color: #ffffff;
    }

    .og-result-icon--image {
        object-fit: cover;
    }

    .og-result-icon--folder {
        background: linear-gradient(160deg, #fbbf24, #f59e0b);
    }

    .og-result-icon--page {
        background: linear-gradient(160deg, #60a5fa, #2563eb);
    }

    .og-result-row-chevron {
        flex: 0 0 auto;
        margin-left: auto;
        font-size: 15px;
        color: #cbd5e1;
    }

    .og-result-tag {
        display: inline-flex;
        align-items: center;
        height: 32px;
        padding: 0 14px;
        border-radius: 9999px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }

    .og-result-tag:hover {
        background: #f1f5f9;
    }

    .og-nsfw-badge {
        display: inline-flex;
        align-items: center;
        height: 18px;
        padding: 0 7px;
        border-radius: 9999px;
        background: #fef2f2;
        color: #b91c1c;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .og-search-more-wrap {
        display: flex;
    }

    .og-search-more-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        padding: 12px 0;
        border-radius: 14px;
        border: 1px solid #e2e8f0 !important;
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 14px;
        font-weight: 600;
    }

    .og-search-more-btn iconify-icon {
        font-size: 16px;
        color: #2563eb;
    }

    .og-search-more-btn:hover {
        background: #f8fafc !important;
    }

    .og-search-more-btn:active {
        background: #e2e8f0 !important;
    }

    @media (max-width: 640px) {
        .og-search-page {
            padding-left: 12px;
            padding-right: 12px;
        }

        .og-search-bar {
            height: 48px;
        }
    }

    html.dark .og-search-box,
    html.dark .og-search-more-btn,
    html.dark .og-search-bar {
        background: #0f172a !important;
        border-color: #1e293b !important;
    }

    html.dark .og-search-bar:focus-within {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .2) !important;
    }

    html.dark .og-search-message {
        color: #94a3b8 !important;
    }

    html.dark .og-search-back-btn:hover,
    body.alma-app.dark .og-search-back-btn:hover {
        background: #1e293b !important;
    }

    html.dark .og-search-pill,
    html.dark .og-search-type-pill {
        background: #0f172a !important;
        border-color: #1e293b !important;
        color: #e2e8f0 !important;
    }

    html.dark .og-search-box-title {
        color: #e2e8f0 !important;
    }

    html.dark .og-search-box-more {
        color: #60a5fa !important;
    }

    html.dark .og-search-box-more iconify-icon {
        color: #60a5fa;
    }

    html.dark .og-search-box-more:hover {
        color: #93c5fd !important;
    }

    html.dark .og-search-pill:hover,
    html.dark .og-search-type-pill:hover {
        background: #1e293b !important;
    }

    html.dark .og-search-pill:active,
    html.dark .og-search-type-pill:active {
        background: #334155 !important;
    }

    html.dark .og-search-pill.is-active,
    html.dark .og-search-type-pill.is-active {
        background: #1e293b !important;
        border-color: #3b82f6 !important;
        color: #60a5fa !important;
    }

    html.dark .og-search-pill.is-active iconify-icon,
    html.dark .og-search-type-pill.is-active iconify-icon {
        color: #3b82f6;
    }

    html.dark .og-search-bar-input {
        color: #e2e8f0;
    }

    html.dark .og-result-row:hover {
        background: #1e293b;
    }

    html.dark .og-result-row:active {
        background: #334155;
    }

    html.dark .og-result-row-thumb {
        background: #1e293b;
    }

    html.dark .og-result-row-thumb--placeholder {
        color: #475569;
    }

    html.dark .og-result-row-chevron {
        color: #475569;
    }

    html.dark .og-search-follow-btn.is-following {
        background: #1e293b !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }

    html.dark .og-result-row-title {
        color: #e2e8f0;
    }
</style>

@php
    $searchI18n = [
        'all' => __('site.search.all'),
        'posts' => __('site.search.posts'),
        'categories' => __('site.search.categories'),
        'tags' => __('site.search.tags'),
        'users' => __('site.search.users'),
        'comments' => __('site.search.comments'),
        'pages' => __('site.search.pages'),
        'sortRelevance' => __('site.search.sort_relevance'),
        'sortNewest' => __('site.search.sort_newest'),
        'sortPopular' => __('site.search.sort_popular'),
        'filterNsfw' => __('site.search.filter_nsfw'),
        'filterAi' => __('site.search.filter_ai'),
        'emptyQuery' => __('site.search.empty_query'),
        'tooShort' => __('site.search.too_short'),
        'disabled' => __('site.search.disabled'),
        'empty' => __('site.search.empty'),
        'showMore' => __('site.search.show_more'),
        'loading' => __('site.search.loading'),
        'views' => __('site.search.views'),
        'followersCount' => __('site.search.followers_count'),
        'postsCount' => __('site.search.posts_count'),
        'inPost' => __('site.search.in_post'),
        'categoryBadge' => __('site.search.category_badge'),
        'searchFailed' => __('site.mobile_nav.search_failed'),
        'follow' => __('site.profile_page.follow'),
        'following' => __('site.profile_page.following'),
    ];
@endphp

@section('content')
    <div class="og-search-page" data-search-page
         data-endpoint="{{ route('search') }}"
         data-initial-query="{{ $query }}"
         data-initial-type="{{ $meta['type'] }}"
         data-initial-sort="{{ $meta['sort'] }}"
         data-initial-nsfw="{{ $meta['nsfw'] ? '1' : '0' }}"
         data-initial-ai="{{ $meta['ai'] ? '1' : '0' }}"
         data-i18n="{{ json_encode($searchI18n, JSON_UNESCAPED_UNICODE) }}"
         data-authenticated="{{ auth()->check() ? '1' : '0' }}"
         data-login-url="{{ route('login') }}"
    >
        <div class="og-search-identity">
            <button type="button" class="og-search-identity__back" data-search-back aria-label="{{ __('site.mobile_nav.back') ?? 'Geri' }}">
                <iconify-icon icon="lucide:arrow-left" aria-hidden="true"></iconify-icon>
            </button>
            <span class="og-search-identity__divider" aria-hidden="true"></span>
            <h2 class="og-search-identity__title">{{ __('site.search.title') }}</h2>

            <div class="og-search-identity__actions">
                <div class="og-search-info" data-search-info>
                    <button
                        type="button"
                        class="og-search-info__trigger"
                        data-search-info-trigger
                        aria-label="{{ __('site.search.info_label') }}"
                        aria-expanded="false"
                    >
                        <iconify-icon icon="lucide:info" aria-hidden="true"></iconify-icon>
                    </button>

                    <div class="og-search-info-popover" data-search-info-popover role="dialog" aria-label="{{ __('site.search.info_label') }}">
                        <button type="button" class="og-search-info-popover__close" data-search-info-close aria-label="{{ __('site.common.close') }}">
                            <iconify-icon icon="lucide:x" aria-hidden="true"></iconify-icon>
                        </button>
                        <span class="og-search-info-popover__icon" aria-hidden="true">
                            <iconify-icon icon="lucide:info"></iconify-icon>
                        </span>
                        <p class="og-search-info-popover__title">{{ __('site.search.info_title') }}</p>
                        <p class="og-search-info-popover__text">{{ __('site.search.info_text') }}</p>
                        <button type="button" class="og-search-info-popover__ok" data-search-info-close>{{ __('site.search.info_ok') }}</button>
                    </div>
                </div>

                <div class="og-search-settings" data-search-settings>
                    <button
                        type="button"
                        class="og-search-settings__trigger"
                        data-search-settings-trigger
                        aria-label="{{ __('site.common.settings') }}"
                        aria-expanded="false"
                    >
                        <iconify-icon icon="lucide:settings-2" aria-hidden="true"></iconify-icon>
                    </button>

                    <div class="og-search-settings-menu" data-search-settings-menu>
                        <div class="og-search-filters">
                            <div>
                                <span class="og-search-settings-menu__label">{{ __('site.search.sort_label') }}</span>
                                <div class="og-search-pills" data-search-sort-pills>
                                    <button type="button" class="og-search-pill og-search-pill--sort" data-sort="relevance"><iconify-icon icon="lucide:sparkles"></iconify-icon>{{ __('site.search.sort_relevance') }}</button>
                                    <button type="button" class="og-search-pill og-search-pill--sort" data-sort="newest"><iconify-icon icon="lucide:clock"></iconify-icon>{{ __('site.search.sort_newest') }}</button>
                                    <button type="button" class="og-search-pill og-search-pill--sort" data-sort="popular"><iconify-icon icon="lucide:flame"></iconify-icon>{{ __('site.search.sort_popular') }}</button>
                                </div>
                            </div>
                            <div>
                                <span class="og-search-settings-menu__label">{{ __('site.search.filter_label') }}</span>
                                <div class="og-search-pills og-search-pills--toggles">
                                    <button type="button" class="og-search-pill og-search-pill--toggle" data-toggle="nsfw"><iconify-icon icon="lucide:eye-off"></iconify-icon>{{ __('site.search.filter_nsfw') }}</button>
                                    <button type="button" class="og-search-pill og-search-pill--toggle" data-toggle="ai"><iconify-icon icon="lucide:bot"></iconify-icon>{{ __('site.search.filter_ai') }}</button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <span class="og-search-settings-menu__label">{{ __('site.search.type_label') }}</span>
                            <div class="og-search-types" data-search-type-pills>
                            <button type="button" class="og-search-type-pill" data-type="all"><iconify-icon icon="lucide:layout-grid"></iconify-icon>{{ __('site.search.all') }}</button>
                            <button type="button" class="og-search-type-pill" data-type="posts"><iconify-icon icon="lucide:file-text"></iconify-icon>{{ __('site.search.posts') }}</button>
                            <button type="button" class="og-search-type-pill" data-type="categories"><iconify-icon icon="lucide:folder"></iconify-icon>{{ __('site.search.categories') }}</button>
                            <button type="button" class="og-search-type-pill" data-type="tags"><iconify-icon icon="lucide:tag"></iconify-icon>{{ __('site.search.tags') }}</button>
                            <button type="button" class="og-search-type-pill" data-type="users"><iconify-icon icon="lucide:users"></iconify-icon>{{ __('site.search.users') }}</button>
                            <button type="button" class="og-search-type-pill" data-type="comments"><iconify-icon icon="lucide:message-circle"></iconify-icon>{{ __('site.search.comments') }}</button>
                            <button type="button" class="og-search-type-pill" data-type="pages"><iconify-icon icon="lucide:file"></iconify-icon>{{ __('site.search.pages') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="og-search-identity__edge-blur" aria-hidden="true"></div>
        </div>
        <div data-identity-spacer aria-hidden="true"></div>

        <div class="og-search-bar-row">
            <div class="og-search-bar">
                <input
                    type="text"
                    id="og-search-page-input"
                    value="{{ $query }}"
                    placeholder="{{ __('site.search.placeholder') }}"
                    class="og-search-bar-input"
                    autocomplete="off"
                    autofocus
                    data-search-query-input
                >
                <iconify-icon
                    icon="lucide:search"
                    class="og-search-bar-icon {{ $query !== '' ? 'hidden' : '' }}"
                    data-search-query-icon
                    aria-hidden="true"
                ></iconify-icon>
                <button type="button" class="og-search-bar-clear {{ $query === '' ? 'hidden' : '' }}" aria-label="{{ __('site.mobile_nav.clear') ?? 'Temizle' }}" data-search-query-clear>
                    <iconify-icon icon="lucide:x"></iconify-icon>
                </button>
            </div>
        </div>

        <div class="og-search-results" data-search-results-container>
            <p class="og-search-message">{{ __('site.search.loading') }}</p>
        </div>

        <div class="og-search-more-wrap hidden" data-search-more-wrap>
            <button type="button" class="og-search-more-btn" data-search-more-btn><iconify-icon icon="lucide:chevron-down"></iconify-icon>{{ __('site.search.show_more') }}</button>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-search-page]');
    if (!root) return;

    const endpoint = root.dataset.endpoint;
    const i18n = JSON.parse(root.dataset.i18n || '{}');

    const queryInput = root.querySelector('[data-search-query-input]');
    const queryClear = root.querySelector('[data-search-query-clear]');
    const queryIcon = root.querySelector('[data-search-query-icon]');
    const backBtn = root.querySelector('[data-search-back]');

    // Mobilde arama filtrelerini barindiran acilir "ayarlar" menusu.
    const settingsRoot = root.querySelector('[data-search-settings]');
    const settingsTrigger = root.querySelector('[data-search-settings-trigger]');
    const settingsMenu = root.querySelector('[data-search-settings-menu]');

    // Ayarlar dislisinin solundaki "bilgi (i)" tetikleyicisi - aramanin
    // sadece site ici herkese acik gonderileri kapsadigini, dis kaynaklarda
    // arama yapilmadigini aciklayan kucuk bir popover acar.
    const infoRoot = root.querySelector('[data-search-info]');
    const infoTrigger = root.querySelector('[data-search-info-trigger]');
    const infoPopover = root.querySelector('[data-search-info-popover]');
    const infoCloseButtons = Array.from(root.querySelectorAll('[data-search-info-close]'));

    let closeSettings = () => {};
    let closeInfo = () => {};

    if (settingsRoot && settingsTrigger && settingsMenu) {
        const openSettings = () => {
            closeInfo();
            settingsRoot.classList.add('is-open');
            settingsMenu.classList.add('is-open');
            settingsTrigger.setAttribute('aria-expanded', 'true');
        };

        closeSettings = () => {
            settingsRoot.classList.remove('is-open');
            settingsMenu.classList.remove('is-open');
            settingsTrigger.setAttribute('aria-expanded', 'false');
        };

        settingsTrigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (settingsMenu.classList.contains('is-open')) closeSettings(); else openSettings();
        });

        settingsRoot.addEventListener('click', (event) => event.stopPropagation());
    }

    if (infoRoot && infoTrigger && infoPopover) {
        const openInfo = () => {
            closeSettings();
            infoRoot.classList.add('is-open');
            infoPopover.classList.add('is-open');
            infoTrigger.setAttribute('aria-expanded', 'true');
        };

        closeInfo = () => {
            infoRoot.classList.remove('is-open');
            infoPopover.classList.remove('is-open');
            infoTrigger.setAttribute('aria-expanded', 'false');
        };

        infoTrigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (infoPopover.classList.contains('is-open')) closeInfo(); else openInfo();
        });

        infoCloseButtons.forEach((btn) => btn.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            closeInfo();
            infoTrigger.focus();
        }));

        infoRoot.addEventListener('click', (event) => event.stopPropagation());
    }

    document.addEventListener('click', () => {
        closeSettings();
        closeInfo();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSettings();
            closeInfo();
        }
    });
    const sortPills = Array.from(root.querySelectorAll('[data-search-sort-pills] [data-sort]'));
    const togglePills = Array.from(root.querySelectorAll('[data-toggle]'));
    const typePills = Array.from(root.querySelectorAll('[data-search-type-pills] [data-type]'));
    const resultsContainer = root.querySelector('[data-search-results-container]');
    const moreWrap = root.querySelector('[data-search-more-wrap]');
    const moreBtn = root.querySelector('[data-search-more-btn]');

    const state = {
        q: root.dataset.initialQuery || '',
        type: root.dataset.initialType || 'all',
        sort: root.dataset.initialSort || 'relevance',
        nsfw: root.dataset.initialNsfw === '1',
        ai: root.dataset.initialAi === '1',
        offset: 0,
    };

    let requestId = 0;
    let debounceTimer = null;
    let accumulated = null;

    const escapeHtml = (value = '') => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const syncPillStates = () => {
        sortPills.forEach((btn) => btn.classList.toggle('is-active', btn.dataset.sort === state.sort));
        togglePills.forEach((btn) => btn.classList.toggle('is-active', Boolean(state[btn.dataset.toggle])));
        typePills.forEach((btn) => btn.classList.toggle('is-active', btn.dataset.type === state.type));
        queryClear.classList.toggle('hidden', !queryInput.value.trim());
        queryIcon?.classList.toggle('hidden', Boolean(queryInput.value.trim()));
    };

    const syncUrl = () => {
        const params = new URLSearchParams();
        if (state.q) params.set('q', state.q);
        if (state.type !== 'all') params.set('type', state.type);
        if (state.sort !== 'relevance') params.set('sort', state.sort);
        if (state.nsfw) params.set('nsfw', '1');
        if (state.ai) params.set('ai', '1');
        const qs = params.toString();
        const url = qs ? `${endpoint}?${qs}` : endpoint;
        window.history.replaceState({}, '', url);
    };

    const avatarHtml = (avatar, label, sizeClass) => avatar
        ? `<img src="${escapeHtml(avatar)}" alt="${escapeHtml(label || '')}" class="${sizeClass} rounded-full object-cover" loading="lazy">`
        : `<span class="${sizeClass} inline-flex items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">${escapeHtml((label || '?').trim().charAt(0).toUpperCase() || '?')}</span>`;

    const boxWrapper = (key, label, count, innerHtml, seeAllHtml = '') => `
        <section class="og-search-box" data-search-box="${key}">
            <div class="og-search-box-head">
                <h2 class="og-search-box-title">${escapeHtml(label)}</h2>
                <span class="og-search-box-count">${count}</span>
            </div>
            <div class="og-search-box-body">${innerHtml}</div>
            ${seeAllHtml}
        </section>
    `;

    const renderPosts = (items) => items.map((post) => `
        <a href="${escapeHtml(post.url)}" class="og-result-row og-result-row--post">
            <div class="og-result-row-main">
                <p class="og-result-row-title">${escapeHtml(post.title)}${post.is_nsfw ? '<span class="og-nsfw-badge">NSFW</span>' : ''}</p>
                ${post.snippet ? `<p class="og-result-row-snippet">${escapeHtml(post.snippet)}</p>` : ''}
                <div class="og-result-row-meta">
                    ${post.category ? `<span>${escapeHtml(post.category)}</span>` : ''}
                    ${post.author ? `<span>${escapeHtml(post.author)}</span>` : ''}
                    <span>${i18n.views.replace(':count', Number(post.views || 0).toLocaleString('tr-TR'))}</span>
                </div>
            </div>
            ${post.image
                ? `<img src="${escapeHtml(post.image)}" alt="${escapeHtml(post.title)}" class="og-result-row-thumb" loading="lazy">`
                : `<span class="og-result-row-thumb og-result-row-thumb--placeholder"><iconify-icon icon="lucide:image" aria-hidden="true"></iconify-icon></span>`
            }
        </a>
    `).join('');

    const renderCategories = (items) => items.map((cat) => `
        <a href="${escapeHtml(cat.url)}" class="og-result-row og-result-row--chip">
            ${cat.avatar
                ? `<img src="${escapeHtml(cat.avatar)}" alt="${escapeHtml(cat.title)}" class="og-result-icon og-result-icon--image" loading="lazy">`
                : `<span class="og-result-icon og-result-icon--folder"><iconify-icon icon="lucide:folder" aria-hidden="true"></iconify-icon></span>`
            }
            <span class="og-result-row-title">${escapeHtml(cat.title)}</span>
            <span class="og-result-row-badge">${i18n.postsCount.replace(':count', Number(cat.posts_count || 0).toLocaleString('tr-TR'))}</span>
            <iconify-icon class="og-result-row-chevron" icon="lucide:chevron-right" aria-hidden="true"></iconify-icon>
        </a>
    `).join('');

    const renderTags = (items) => items.map((tag) => `
        <a href="${escapeHtml(tag.url)}" class="og-result-tag">#${escapeHtml(tag.title)}</a>
    `).join('');

    const renderUsers = (items) => items.map((user) => `
        <a href="${escapeHtml(user.url || '#')}" class="og-result-row og-result-row--chip">
            ${avatarHtml(user.avatar, user.title, 'og-result-avatar')}
            <span class="og-result-row-user">
                <span class="og-result-row-title">${escapeHtml(user.title)}</span>
                ${user.subtitle ? `<span class="og-result-row-subtitle">${escapeHtml(user.subtitle)}</span>` : ''}
            </span>
            ${user.is_self ? '' : `
                <button type="button"
                    class="og-search-follow-btn ${user.is_following ? 'is-following' : ''}"
                    data-follow-btn
                    data-follow-username="${escapeHtml(user.username || '')}"
                    data-following="${user.is_following ? '1' : '0'}"
                >${user.is_following ? escapeHtml(i18n.following) : escapeHtml(i18n.follow)}</button>
            `}
        </a>
    `).join('');

    const renderComments = (items) => items.map((comment) => `
        <a href="${escapeHtml(comment.url || '#')}" class="og-result-row og-result-row--comment">
            ${avatarHtml(comment.author_avatar, comment.author, 'og-result-avatar')}
            <div class="og-result-row-main">
                <p class="og-result-row-snippet">${escapeHtml(comment.snippet)}</p>
                <div class="og-result-row-meta">
                    <span>${escapeHtml(comment.author || '')}</span>
                    ${comment.post_title ? `<span>${i18n.inPost.replace(':title', comment.post_title)}</span>` : ''}
                </div>
            </div>
        </a>
    `).join('');

    const renderPages = (items) => items.map((page) => `
        <a href="${escapeHtml(page.url)}" class="og-result-row og-result-row--page">
            <span class="og-result-icon og-result-icon--page"><iconify-icon icon="lucide:file-text" aria-hidden="true"></iconify-icon></span>
            <div class="og-result-row-main">
                <p class="og-result-row-title">${escapeHtml(page.title)}</p>
                ${page.snippet ? `<p class="og-result-row-snippet">${escapeHtml(page.snippet)}</p>` : ''}
            </div>
            <iconify-icon class="og-result-row-chevron" icon="lucide:chevron-right" aria-hidden="true"></iconify-icon>
        </a>
    `).join('');

    const typeRenderers = {
        posts: { render: renderPosts, label: i18n.posts },
        categories: { render: renderCategories, label: i18n.categories },
        tags: { render: renderTags, label: i18n.tags },
        users: { render: renderUsers, label: i18n.users },
        comments: { render: renderComments, label: i18n.comments },
        pages: { render: renderPages, label: i18n.pages },
    };

    const renderMessage = (message) => {
        resultsContainer.innerHTML = `<p class="og-search-message">${escapeHtml(message)}</p>`;
        moreWrap.classList.add('hidden');
    };

    const renderAllTypes = (data) => {
        const boxes = [];
        let total = 0;

        Object.keys(typeRenderers).forEach((key) => {
            const items = Array.isArray(data[key]) ? data[key] : [];
            if (!items.length) return;
            total += items.length;
            const { render, label } = typeRenderers[key];
            const isFlow = key === 'tags';
            const inner = render(items);
            boxes.push(boxWrapper(
                key,
                label,
                items.length,
                `<div class="${isFlow ? 'og-result-flow' : 'og-result-list'}">${inner}</div>`,
                `<button type="button" class="og-search-box-more" data-search-jump-type="${key}"><iconify-icon icon="lucide:chevron-down"></iconify-icon>${i18n.showMore}</button>`,
            ));
        });

        if (!total) {
            renderMessage(i18n.empty);
            return;
        }

        resultsContainer.innerHTML = boxes.join('');
        moreWrap.classList.add('hidden');

        resultsContainer.querySelectorAll('[data-search-jump-type]').forEach((btn) => {
            btn.addEventListener('click', () => {
                state.type = btn.dataset.searchJumpType;
                state.offset = 0;
                accumulated = null;
                syncPillStates();
                syncUrl();
                runSearch();
            });
        });
    };

    const renderSingleType = (data, hasMore, append = false) => {
        const key = state.type;
        const { render, label } = typeRenderers[key];
        const items = Array.isArray(data[key]) ? data[key] : [];

        if (append && accumulated) {
            accumulated = accumulated.concat(items);
        } else {
            accumulated = items;
        }

        if (!accumulated.length) {
            renderMessage(i18n.empty);
            return;
        }

        const isFlow = key === 'tags';
        const inner = render(accumulated);
        resultsContainer.innerHTML = boxWrapper(key, label, accumulated.length, `<div class="${isFlow ? 'og-result-flow' : 'og-result-list'}">${inner}</div>`);
        moreWrap.classList.toggle('hidden', !hasMore);
    };

    const runSearch = async (append = false) => {
        const myRequestId = ++requestId;
        const q = state.q.trim();

        if (!q) {
            renderMessage(i18n.emptyQuery);
            return;
        }

        if (!append) {
            resultsContainer.innerHTML = `<p class="og-search-message">${escapeHtml(i18n.loading)}</p>`;
        }

        const params = new URLSearchParams({ q, type: state.type, sort: state.sort });
        if (state.nsfw) params.set('nsfw', '1');
        if (state.ai) params.set('ai', '1');
        if (append) params.set('offset', String(state.offset));

        try {
            const response = await fetch(`${endpoint}?${params.toString()}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await response.json();

            if (myRequestId !== requestId) return;

            if (json.meta && json.meta.too_short) {
                renderMessage(i18n.tooShort.replace(':min', String(json.meta.min_length ?? 2)));
                return;
            }

            if (json.meta && !json.meta.enabled) {
                renderMessage(i18n.disabled);
                return;
            }

            if (state.type === 'all') {
                renderAllTypes(json.data || {});
            } else {
                renderSingleType(json.data || {}, Boolean(json.meta && json.meta.has_more), append);
            }
        } catch (error) {
            if (myRequestId !== requestId) return;
            renderMessage(i18n.searchFailed || 'Sonuc alinamadi.');
        }
    };

    queryInput.addEventListener('input', () => {
        syncPillStates();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            state.q = queryInput.value;
            state.offset = 0;
            accumulated = null;
            syncUrl();
            runSearch();
        }, 220);
    });

    queryClear.addEventListener('click', () => {
        queryInput.value = '';
        state.q = '';
        syncPillStates();
        syncUrl();
        renderMessage(i18n.emptyQuery);
        queryInput.focus();
    });

    if (backBtn) {
        backBtn.addEventListener('click', () => {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '{{ route('home') }}';
            }
        });
    }

    sortPills.forEach((btn) => btn.addEventListener('click', () => {
        state.sort = btn.dataset.sort;
        state.offset = 0;
        accumulated = null;
        syncPillStates();
        syncUrl();
        runSearch();
    }));

    togglePills.forEach((btn) => btn.addEventListener('click', () => {
        const key = btn.dataset.toggle;
        state[key] = !state[key];
        state.offset = 0;
        accumulated = null;
        syncPillStates();
        syncUrl();
        runSearch();
    }));

    typePills.forEach((btn) => btn.addEventListener('click', () => {
        state.type = btn.dataset.type;
        state.offset = 0;
        accumulated = null;
        syncPillStates();
        syncUrl();
        runSearch();
    }));

    moreBtn.addEventListener('click', () => {
        state.offset = (accumulated ? accumulated.length : 0);
        runSearch(true);
    });

    const isAuthenticated = root.dataset.authenticated === '1';
    const loginUrl = root.dataset.loginUrl || '/login';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    resultsContainer.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-follow-btn]');
        if (!btn) return;

        event.preventDefault();
        event.stopPropagation();

        if (!isAuthenticated) {
            window.location.href = loginUrl;
            return;
        }

        const username = btn.dataset.followUsername;
        if (!username || btn.disabled) return;

        btn.disabled = true;

        fetch(`/u/${encodeURIComponent(username)}/follow`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
        })
            .then((response) => {
                if (!response.ok) throw new Error('follow request failed');
                return response.json();
            })
            .then((data) => {
                const following = Boolean(data.following);
                btn.dataset.following = following ? '1' : '0';
                btn.classList.toggle('is-following', following);
                btn.textContent = following ? i18n.following : i18n.follow;
            })
            .catch(() => {})
            .finally(() => {
                btn.disabled = false;
            });
    });

    syncPillStates();
    if (state.q.trim()) {
        runSearch();
    } else {
        renderMessage(i18n.emptyQuery);
    }
});
</script>
@endpush
