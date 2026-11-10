@extends('layouts.app')

@section('title', 'OgraFi Santral — Bulut Santral ve İletişim Çözümleri')
@section('description', 'OgraFi Santral ile işletmenize özel bulut santral, IVR, kurumsal telefon numarası, WhatsApp Business ve CRM entegrasyonları.')
@section('canonical', route('santral'))
@section('og_type', 'website')

@php
    $santralSlides = collect($santralSettings?->slides ?? [])->filter(fn ($slide) => filled($slide['image'] ?? null))->values();
    $santralFeatures = $santralSettings?->features ?: [
        ['icon' => 'phone-call', 'title' => 'Bulut santral', 'description' => 'Ofis dışında olsanız da ekibiniz aynı telefon sistemi üzerinden çağrı alıp yapabilir. Kullanıcı ve dahili yapısını işletmenize göre hazırlıyoruz.'],
        ['icon' => 'list', 'title' => 'Sesli yanıt sistemi (IVR)', 'description' => 'Karşılama ve tuşlama menüleriyle arayanları doğru ekibe yönlendirin.'],
        ['icon' => 'phone', 'title' => 'Kurumsal telefon numaraları', 'description' => '0850, 0312, 0212, 0216 ve 0232 alan kodlarında numara çözümleri için başvuru ve yapılandırma desteği.'],
        ['icon' => 'message-circle', 'title' => 'WhatsApp Business', 'description' => 'Müşteri iletişim süreçlerinizi WhatsApp Business çözümleriyle iş akışlarınıza bağlayın.'],
        ['icon' => 'shield-check', 'title' => 'OTP ve SMS', 'description' => 'Doğrulama kodu ve bilgilendirme mesajları için SMS/OTP süreçlerinizi yapılandırın.'],
        ['icon' => 'workflow', 'title' => 'CRM, API ve Webhook', 'description' => 'Çağrı ve müşteri süreçlerinizi CRM veya özel yazılımlarla entegre edin.'],
    ];
    $santralSteps = $santralSettings?->steps ?: [
        ['title' => 'İhtiyacınızı belirleyelim', 'description' => 'Hat, kullanıcı, dahili, çağrı akışı ve entegrasyon ihtiyaçlarınızı birlikte netleştirelim.'],
        ['title' => 'Sisteminizi hazırlayalım', 'description' => 'Santrali kurup numara, karşılama, yönlendirme ve kullanıcı ayarlarını yapılandıralım.'],
        ['title' => 'Devreye alıp destek verelim', 'description' => 'Testlerin ardından sistemi kullanıma açalım; ihtiyaç duyduğunuzda teknik destek sağlayalım.'],
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
                <span class="ografi-pbx-eyebrow">{{ $santralSettings?->eyebrow ?: 'OGRAFI SANTRAL' }}</span>
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
                        <span class="ografi-pbx-feature-icon"><x-repost-icon :name="$feature['icon'] ?? 'phone'" size="21" /></span>
                        <h3>{{ $feature['title'] ?? '' }}</h3>
                        <p>{{ $feature['description'] ?? '' }}</p>
                    </section>
                @endforeach
            </div>
        </section>

        <section class="ografi-pbx-section">
            <h2>{{ $santralSettings?->steps_title ?: 'Kurulumla uğraşmayın, biz yönetelim' }}</h2>
            <ol class="ografi-pbx-steps">
                @foreach ($santralSteps as $step)
                    <li><strong>{{ $step['title'] ?? '' }}</strong><span>{{ $step['description'] ?? '' }}</span></li>
                @endforeach
            </ol>
        </section>

        <section class="ografi-pbx-bottom">
            <div>
                <h2>{{ $santralSettings?->bottom_title ?: 'İşletmeniz için doğru santral çözümünü birlikte kuralım.' }}</h2>
                <p>{{ $santralSettings?->bottom_description ?: 'İhtiyacınızı ve mevcut altyapınızı anlatın; size uygun kurulum ve hizmet kapsamını değerlendirelim.' }}</p>
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
