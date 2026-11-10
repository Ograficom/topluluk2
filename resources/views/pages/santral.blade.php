@extends('layouts.app')

@section('title', 'OgraFi Santral — Bulut Santral ve İletişim Çözümleri')
@section('description', 'OgraFi Santral ile işletmenize özel bulut santral, IVR, kurumsal telefon numarası, WhatsApp Business ve CRM entegrasyonları. Kurulum, yapılandırma ve teknik destek tek noktada.')
@section('canonical', route('santral'))
@section('og_type', 'website')

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
            'description' => 'İşletmelere özel bulut santral, IVR, telefon numarası ve entegrasyon hizmetleri.',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
@endpush

@section('content')
    <article class="card card-body ografi-pbx-page">
        <div class="ografi-pbx-hero">
            <span class="ografi-pbx-eyebrow">OGRAFI SANTRAL</span>
            <h1>İşletmenizin iletişimi, tek merkezden.</h1>
            <p class="ografi-pbx-lead">Telefon trafiğinizi profesyonel bir bulut santralle yönetin. Numara, karşılama menüsü, ekip yönlendirmesi ve entegrasyonları sizin için kurup yapılandıralım.</p>
            <a class="btn btn-primary btn-lg" href="{{ route('contacts', ['subject' => 'OgraFi Santral hizmeti', 'recipient' => 'partnerships']) }}">
                <x-repost-icon name="message" size="18" />
                Santral hizmeti için iletişime geç
            </a>
            <p class="small color-gray">Hazır bir script satmıyoruz; kurulumdan yapılandırmaya ve teknik desteğe kadar hizmet sunuyoruz.</p>
        </div>

        <div class="ografi-pbx-section">
            <h2>İşletmenize uygun santral çözümleri</h2>
            <div class="ografi-pbx-grid">
                <section class="ografi-pbx-feature">
                    <x-repost-icon name="message" size="22" />
                    <h3>Bulut santral</h3>
                    <p of="">Ofis dışında olsanız da ekibiniz aynı telefon sistemi üzerinden çağrı alıp yapabilir. Kullanıcı ve dahili yapılandırmasını işletmenize göre hazırlıyoruz.</p>
                </section>
                <section class="ografi-pbx-feature">
                    <x-repost-icon name="list" size="22" />
                    <h3>Sesli yanıt sistemi (IVR)</h3>
                    <p>“Satış için 1, destek için 2” gibi karşılama ve tuşlama menüleriyle arayanları doğru ekibe yönlendirin.</p>
                </section>
                <section class="ografi-pbx-feature">
                    <x-repost-icon name="user" size="22" />
                    <h3>Kurumsal telefon numaraları</h3>
                    <p>İhtiyacınıza göre 0850, 0312, 0212, 0216 ve 0232 alan kodlarında numara çözümleri için başvuru ve yapılandırma desteği.</p>
                </section>
                <section class="ografi-pbx-feature">
                    <x-repost-icon name="message" size="22" />
                    <h3>WhatsApp Business</h3>
                    <p>Müşteri iletişim süreçlerinizi WhatsApp Business çözümleriyle santral ve iş akışlarınıza bağlayın.</p>
                </section>
                <section class="ografi-pbx-feature">
                    <x-repost-icon name="security" size="22" />
                    <h3>OTP ve SMS</h3>
                    <p>Doğrulama kodu ve bilgilendirme mesajları için SMS/OTP süreçlerinizi ihtiyacınıza göre yapılandırın.</p>
                </section>
                <section class="ografi-pbx-feature">
                    <x-repost-icon name="link" size="22" />
                    <h3>CRM, API ve Webhook</h3>
                    <p>Çağrı ve müşteri süreçlerinizi kullandığınız CRM veya özel yazılımlarla entegre edin.</p>
                </section>
            </div>
        </div>

        <div class="ografi-pbx-section">
            <h2>Kurulumla uğraşmayın, biz yönetelim</h2>
            <ol class="ografi-pbx-steps">
                <li><strong>İhtiyacınızı belirleyelim</strong><span>Hat, kullanıcı, dahili, çağrı akışı ve entegrasyon ihtiyaçlarınızı birlikte netleştirelim.</span></li>
                <li><strong>Sisteminizi hazırlayalım</strong><span>Santrali kurup numara, karşılama, yönlendirme ve kullanıcı ayarlarını yapılandıralım.</span></li>
                <li><strong>Devreye alıp destek verelim</strong><span>Testlerin ardından sistemi kullanıma açalım; ihtiyaç duyduğunuzda teknik destek sağlayalım.</span></li>
            </ol>
        </div>

        <div class="ografi-pbx-bottom">
            <div>
                <h2>İşletmeniz için doğru santral çözümünü birlikte kuralım.</h2>
                <p>İhtiyacınızı ve mevcut altyapınızı anlatın; size uygun kurulum ve hizmet kapsamını değerlendirelim.</p>
            </div>
            <a class="btn btn-primary btn-lg" href="{{ route('contacts', ['subject' => 'OgraFi Santral hizmeti', 'recipient' => 'partnerships']) }}">Teklif ve bilgi alın</a>
        </div>
    </article>
@endsection
