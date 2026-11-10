@extends('layouts.app')

@section('title', 'OgraFi Santral — Bulut Santral ve İletişim Çözümleri')
@section('description', 'OgraFi Santral ile işletmenize özel bulut santral, IVR, kurumsal telefon numarası, WhatsApp Business ve CRM entegrasyonları.')
@section('canonical', route('santral'))
@section('og_type', 'website')

@php
    $santralSlides = collect($santralSettings?->slides ?? [])->filter(fn ($slide) => filled($slide['image'] ?? null))->values();
    $santralFeatures = $santralSettings?->features ?: [
        ['icon' => 'channels', 'title' => 'Bulut santral hizmeti', 'description' => 'Bulut santral altyapısını biz sağlıyor, işletmenizin hatlarını, dahili kullanıcılarını ve çağrı yönlendirmelerini sizin adınıza kurup yönetiyoruz.'],
        ['icon' => 'list', 'title' => 'IVR ve profesyonel karşılama', 'description' => 'İşletmenize özel sesli karşılama ve tuşlama menülerini hazırlıyor, aramaları doğru departman ve kişilere yönlendiriyoruz.'],
        ['icon' => 'megaphone', 'title' => '0850 ve şehir numaraları', 'description' => '0850, 0312, 0212, 0216 ve 0232 numara talepleriniz için başvuru, operatör bağlantısı ve santral tanımlamasını sağlıyoruz.'],
        ['icon' => 'message', 'title' => 'WhatsApp Business entegrasyonu', 'description' => 'WhatsApp Business bağlantısını ve müşteri iletişim akışlarını işletmenizin kullandığı süreçlere göre yapılandırıyoruz.'],
        ['icon' => 'security', 'title' => 'SMS ve OTP entegrasyonu', 'description' => 'Doğrulama kodu ve bilgilendirme mesajları için SMS/OTP bağlantılarını kuruyor, test ediyor ve devreye alıyoruz.'],
        ['icon' => 'bolt', 'title' => 'CRM, API ve Webhook bağlantıları', 'description' => 'Santral çağrı olaylarını CRM ve özel yazılımlarınıza bağlıyor; ihtiyaç duyulan API ve Webhook akışlarını yapılandırıyoruz.'],
    ];
    $santralPackages = $santralSettings?->packages ?: [
        ['name' => 'Başlangıç', 'price' => 0, 'period' => 'aylık', 'description' => 'Temel telefon trafiği ve çağrı yönlendirme ihtiyaçları için.', 'features' => "Bulut santral\nTemel karşılama\nÇağrı yönlendirme\nTeknik destek", 'featured' => false],
        ['name' => 'İşletme', 'price' => 0, 'period' => 'aylık', 'description' => 'Günlük çağrı trafiği olan işletmeler için kapsamlı paket.', 'features' => "Tüm Başlangıç özellikleri\nIVR sesli yanıt menüsü\nBirden fazla dahili\nÇağrı raporları\nEntegrasyon desteği", 'featured' => true],
        ['name' => 'Profesyonel', 'price' => 0, 'period' => 'aylık', 'description' => 'Yoğun çağrı ve özel entegrasyon ihtiyacı olan ekipler için.', 'features' => "Tüm İşletme özellikleri\nGelişmiş çağrı akışları\nCRM / API entegrasyonu\nÖzel yapılandırma\nÖncelikli destek", 'featured' => false],
    ];
    $santralSteps = $santralSettings?->steps ?: [
        ['title' => 'İhtiyacınızı belirleyelim', 'description' => 'Hat, kullanıcı, dahili, çağrı akışı ve entegrasyon ihtiyaçlarınızı birlikte netleştirelim.'],
        ['title' => 'Sisteminizi hazırlayalım', 'description' => 'Hazır santral altyapısını hesabınıza tanımlayıp numara ve kullanıcı ayarlarınızı yapalım.'],
        ['title' => 'Devreye alıp destek verelim', 'description' => 'Kontrollerin ardından, başvurunuz onaylandığında hizmetinizi kullanıma açalım.'],
    ];
@endphp

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'OgraFi Santral',
            'serviceType' => 'Bulut santral ve kurumsal iletişim çözümleri',
            'provider' => ['@type' => 'Organization', 'name' => 'OgraFi', 'url' => route('posts.index')],
            'areaServed' => ['@type' => 'Country', 'name' => 'Türkiye'],
            'url' => route('santral'),
            'description' => $santralSettings?->hero_description ?: 'İşletmelere özel bulut santral, IVR, telefon numarası ve entegrasyon hizmetleri.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
@endpush

@section('content')
    <article class="card card-body ografi-pbx-page">
        <section class="ografi-pbx-hero">
            <div class="ografi-pbx-hero-copy">
                <h1>{{ $santralSettings?->hero_title ?: 'İşletmenizin iletişimi, tek merkezden.' }}</h1>
                <p class="ografi-pbx-lead">{{ $santralSettings?->hero_description ?: 'Telefon trafiğinizi profesyonel bir bulut santralle yönetin. Numara, karşılama menüsü, ekip yönlendirmesi ve entegrasyonları sizin için kurup yapılandıralım.' }}</p>
                <div class="ografi-pbx-actions">
                    <a class="btn btn-primary btn-lg" href="{{ route('contacts', ['subject' => 'OgraFi Santral hizmeti', 'recipient' => 'partnerships']) }}">
                        <x-repost-icon name="message" size="18" />
                        {{ $santralSettings?->primary_button_text ?: 'Santral hizmeti için iletişime geç' }}
                    </a>
                    <button class="btn btn-secondary btn-lg ografi-pbx-activation-trigger" type="button" data-santral-activation-open>
                        <x-repost-icon name="plug" size="18" />
                        {{ $santralSettings?->activation_button_text ?: 'Entegrasyona başla' }}
                    </button>
                </div>
                <p class="small color-gray">{{ $santralSettings?->hero_note ?: 'Hazır bir script satmıyoruz; kurulumdan yapılandırmaya ve teknik desteğe kadar hizmet sunuyoruz.' }}</p>
            </div>
            <div class="ografi-pbx-hero-art" aria-hidden="true">
                <div class="ografi-pbx-art-orbit ografi-pbx-art-orbit--one"></div>
                <div class="ografi-pbx-art-orbit ografi-pbx-art-orbit--two"></div>
                <div class="ografi-pbx-art-card">
                    <span class="ografi-pbx-art-dot"></span>
                    <x-repost-icon name="headset" size="42" />
                    <strong>OgraFi Santral</strong>
                    <small>İletişiminiz tek merkezde</small>
                    <div class="ografi-pbx-art-bars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                </div>
                <span class="ografi-pbx-art-chip ografi-pbx-art-chip--top"><x-repost-icon name="phone-call" size="15" /> Akıllı çağrı yönetimi</span>
                <span class="ografi-pbx-art-chip ografi-pbx-art-chip--bottom"><x-repost-icon name="workflow" size="15" /> Entegrasyona hazır</span>
            </div>
        </section>

        @if ($santralSlides->isNotEmpty())
            <section class="ografi-pbx-slider" aria-label="Santral çözümleri slaytları" data-santral-slider>
                <div class="ografi-pbx-slider-track">
                    @foreach ($santralSlides as $index => $slide)
                        @php $slideImage = \Illuminate\Support\Facades\Storage::disk('public')->url($slide['image']); @endphp
                        <article class="ografi-pbx-slide {{ $index === 0 ? 'is-active' : '' }}" data-santral-slide>
                            <img src="{{ $slideImage }}" alt="{{ $slide['title'] ?? 'OgraFi Santral' }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                            <div class="ografi-pbx-slide-overlay">
                                @if (filled($slide['title'] ?? null))<h2>{{ $slide['title'] }}</h2>@endif
                                @if (filled($slide['description'] ?? null))<p>{{ $slide['description'] }}</p>@endif
                                @if (filled($slide['link'] ?? null))<a href="{{ $slide['link'] }}" target="_blank" rel="noopener noreferrer">Detayları incele <x-repost-icon name="arrow-up-right" size="15" /></a>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
                @if ($santralSlides->count() > 1)
                    <div class="ografi-pbx-slider-controls">
                        <button type="button" data-santral-prev aria-label="Önceki slayt"><x-repost-icon name="chevron-left" size="18" /></button>
                        <div class="ografi-pbx-slider-dots">
                            @foreach ($santralSlides as $index => $slide)
                                <button type="button" data-santral-dot="{{ $index }}" class="{{ $index === 0 ? 'is-active' : '' }}" aria-label="{{ $index + 1 }}. slayt"></button>
                            @endforeach
                        </div>
                        <button type="button" data-santral-next aria-label="Sonraki slayt"><x-repost-icon name="chevron-right" size="18" /></button>
                    </div>
                @endif
            </section>
        @endif

        <section class="ografi-pbx-section">
            <h2>{{ $santralSettings?->features_title ?: 'İşletmenize uygun santral çözümleri' }}</h2>
            <div class="ografi-pbx-grid">
                @foreach ($santralFeatures as $feature)
                    <section class="ografi-pbx-feature">
                        <span class="ografi-pbx-feature-icon"><x-repost-icon :name="in_array($feature['icon'] ?? '', ['channels', 'list', 'megaphone', 'message', 'security', 'bolt'], true) ? $feature['icon'] : ['channels', 'list', 'megaphone', 'message', 'security', 'bolt'][$loop->index % 6]" size="23" /></span>
                        <h3>{{ $feature['title'] ?? '' }}</h3>
                        <p>{{ $feature['description'] ?? '' }}</p>
                    </section>
                @endforeach
            </div>
        </section>

        <section class="ografi-pbx-section ografi-pbx-pricing" id="santral-paketleri">
            <div class="ografi-pbx-section-heading">
                <div>
                    <span class="ografi-pbx-section-kicker">ŞEFFAF FİYATLANDIRMA</span>
                    <h2>{{ $santralSettings?->packages_title ?: 'İşletmeniz için uygun fiyatlı hazır santral paketleri' }}</h2>
                    <p>{{ $santralSettings?->packages_description ?: 'İhtiyacınıza uygun paketi seçin. Başvurunuz incelenip onaylandıktan ve aktivasyon tamamlandıktan sonra santral hizmetiniz kullanıma açılır.' }}</p>
                </div>
            </div>
            <div class="ografi-pbx-package-carousel">
                <button class="ografi-pbx-package-arrow is-prev" type="button" data-santral-package-prev aria-label="Önceki paket"><x-repost-icon name="chevron-left" size="19" /></button>
                <div class="ografi-pbx-pricing-grid" data-santral-package-track tabindex="0" aria-label="Santral paketleri">
                @foreach ($santralPackages as $package)
                    @php($packageFeatures = collect(preg_split('/\\r?\\n/', $package['features'] ?? ''))->filter())
                    <article class="ografi-pbx-plan {{ !empty($package['featured']) ? 'is-featured' : '' }}">
                        @if (!empty($package['featured']))<span class="ografi-pbx-plan-badge">En çok tercih edilen</span>@endif
                        <div class="ografi-pbx-plan-head">
                            <span class="ografi-pbx-plan-icon"><x-repost-icon :name="$package['icon'] ?? ['channels', 'bolt', 'security'][$loop->index % 3]" size="23" /></span>
                            <h3>{{ $package['name'] ?? 'Santral paketi' }}</h3>
                            <p>{{ $package['description'] ?? '' }}</p>
                        </div>
                        <div class="ografi-pbx-plan-price">
                            @if ((float) ($package['price'] ?? 0) > 0)
                                <strong>{{ number_format((float) $package['price'], 0, ',', '.') }} TL</strong><span>/ {{ $package['period'] ?? 'aylık' }}</span>
                            @else
                                <strong>Teklif alın</strong><span>ihtiyacınıza göre</span>
                            @endif
                        </div>
                        <ul>
                            @foreach ($packageFeatures as $feature)
                                <li><x-repost-icon name="check-circle" size="17" /> <span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>
                        <a class="btn {{ !empty($package['featured']) ? 'btn-primary' : 'btn-secondary' }} btn-lg" href="{{ route('contacts', ['subject' => 'OgraFi Santral - ' . ($package['name'] ?? 'Paket') . ' başvurusu', 'recipient' => 'partnerships']) }}">
                            Paketi seç <x-repost-icon name="arrow-right" size="17" />
                        </a>
                        <small>Hizmet, başvuru ve aktivasyon onayından sonra açılır.</small>
                    </article>
                @endforeach
                </div>
                <button class="ografi-pbx-package-arrow is-next" type="button" data-santral-package-next aria-label="Sonraki paket"><x-repost-icon name="chevron-right" size="19" /></button>
            </div>
            <p class="ografi-pbx-swipe-hint">Paketleri görmek için sağa veya sola kaydırın</p>
        </section>

        <section class="ografi-pbx-section ografi-pbx-infrastructure">
            <div class="ografi-pbx-infra-copy">
                <span class="ografi-pbx-section-kicker">KULLANDIĞIMIZ ALTYAPI</span>
                <h2>Güvenilir telefon altyapısı, açık standartlar</h2>
                <p>OgraFi Santral, doğrudan kurulu Asterisk 22 PBX ve PJSIP teknolojileri üzerinde çalışır. Operatör bağlantısı Netgsm SIP trunk üzerinden sağlanır.</p>
            </div>
            <div class="ografi-pbx-tech-grid">
                <div><span class="ografi-pbx-tech-logo ografi-pbx-logo-asterisk" aria-label="Asterisk resmi logosu"><img src="https://upload.wikimedia.org/wikipedia/commons/2/20/Asterisk_logo.svg" alt="Asterisk" loading="lazy"></span><strong>Asterisk 22</strong><small>IP PBX santral motoru</small></div>
                <div><span class="ografi-pbx-tech-logo ografi-pbx-logo-pjsip" aria-label="PJSIP resmi proje adı"><span class="ografi-pbx-pjsip-wordmark">PJSIP</span></span><strong>PJSIP</strong><small>SIP uçları ve trunk bağlantıları</small></div>
                <div><span class="ografi-pbx-tech-logo ografi-pbx-logo-netgsm" aria-label="Netgsm resmi logosu"><img src="https://cdn.netgsm.com.tr/images/logos/netgsm/dark/netgsm-logo.png" alt="Netgsm" loading="lazy"></span><strong>Netgsm SIP</strong><small>Operatör ve telefon hattı bağlantısı</small></div>
                <div><span class="ografi-pbx-tech-logo ografi-pbx-logo-sip" aria-label="SIP protokolü"><span class="ografi-pbx-sip-wordmark">SIP</span></span><strong>SIP standardı</strong><small>Uyumlu IP telefon ve softphone desteği</small></div>
            </div>
        </section>

        <section class="ografi-pbx-section">
            <h2>{{ $santralSettings?->steps_title ?: 'Uygun fiyatlı, hazır bulut santral' }}</h2>
            <ol class="ografi-pbx-steps">
                @foreach ($santralSteps as $step)
                    <li><strong>{{ $step['title'] ?? '' }}</strong><span>{{ $step['description'] ?? '' }}</span></li>
                @endforeach
            </ol>
        </section>

        <section class="ografi-pbx-bottom">
            <div>
                <h2>{{ $santralSettings?->bottom_title ?: 'İşletmeniz için doğru santral çözümünü birlikte kuralım.' }}</h2>
                <p>{{ $santralSettings?->bottom_description ?: 'Size uygun paketi seçin. Başvurunuz incelenip onaylandıktan ve gerekli aktivasyon tamamlandıktan sonra santral hizmetinizi kullanmaya başlayın.' }}</p>
            </div>
            <a class="btn btn-primary btn-lg" href="{{ route('contacts', ['subject' => 'OgraFi Santral hizmeti', 'recipient' => 'partnerships']) }}">Teklif ve bilgi alın</a>
        </section>
    </article>

    <dialog class="ografi-pbx-activation-modal" data-santral-activation-modal aria-labelledby="santral-activation-title">
        <button class="ografi-pbx-modal-close" type="button" data-santral-activation-close aria-label="Kapat"><x-repost-icon name="x" size="19" /></button>
        <div class="ografi-pbx-modal-icon"><x-repost-icon name="plug" size="28" /></div>
        <span class="ografi-pbx-modal-eyebrow">OGRAFI SANTRAL</span>
        <h2 id="santral-activation-title">Hesabınızın santral hizmeti aktivasyonu yapılmamış.</h2>
        <p>Entegrasyonu kullanmaya başlamadan önce santral hizmetinizin hesabınız için etkinleştirilmesi gerekiyor. Aktivasyon ve kurulum için bizimle iletişime geçebilirsiniz.</p>
        <a class="btn btn-primary btn-lg" href="{{ route('contacts', ['subject' => 'Santral hizmeti aktivasyonu', 'recipient' => 'partnerships']) }}">
            <x-repost-icon name="message-circle" size="18" /> Aktivasyon için iletişime geç
        </a>
        <button class="ografi-pbx-modal-dismiss" type="button" data-santral-activation-close>Şimdi değil</button>
    </dialog>
@endsection

@push('scripts')
<script>
(() => {
    const modal = document.querySelector('[data-santral-activation-modal]');
    if (modal) {
        document.querySelectorAll('[data-santral-activation-open]').forEach((button) => button.addEventListener('click', () => modal.showModal()));
        modal.querySelectorAll('[data-santral-activation-close]').forEach((button) => button.addEventListener('click', () => modal.close()));
        modal.addEventListener('click', (event) => {
            if (event.target === modal) modal.close();
        });
    }

    const packageTrack = document.querySelector('[data-santral-package-track]');
    if (packageTrack) {
        const scrollPackages = (direction) => {
            const card = packageTrack.querySelector('.ografi-pbx-plan');
            const step = card ? card.getBoundingClientRect().width + 14 : packageTrack.clientWidth * 0.85;
            packageTrack.scrollBy({ left: direction * step, behavior: 'smooth' });
        };
        document.querySelector('[data-santral-package-prev]')?.addEventListener('click', () => scrollPackages(-1));
        document.querySelector('[data-santral-package-next]')?.addEventListener('click', () => scrollPackages(1));
    }

    const slider = document.querySelector('[data-santral-slider]');
    if (!slider) return;
    const slides = [...slider.querySelectorAll('[data-santral-slide]')];
    const dots = [...slider.querySelectorAll('[data-santral-dot]')];
    if (slides.length < 2) return;
    let active = 0;
    let timer;
    const show = (index) => {
        active = (index + slides.length) % slides.length;
        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === active));
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === active));
    };
    const restart = () => {
        window.clearInterval(timer);
        timer = window.setInterval(() => show(active + 1), 5000);
    };
    slider.querySelector('[data-santral-prev]')?.addEventListener('click', () => { show(active - 1); restart(); });
    slider.querySelector('[data-santral-next]')?.addEventListener('click', () => { show(active + 1); restart(); });
    dots.forEach((dot, i) => dot.addEventListener('click', () => { show(i); restart(); }));
    slider.addEventListener('mouseenter', () => window.clearInterval(timer));
    slider.addEventListener('mouseleave', restart);
    restart();
})();
</script>
@endpush
