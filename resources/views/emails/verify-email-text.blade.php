{{ $brandName }} | E-posta adresi doğrulama

Merhaba{{ filled($user->name ?? null) ? ' '.$user->name : '' }},

{{ $brandName }} topluluğuna katıldığın için teşekkürler. Hesabını güvence altına almak ve üyeliğini tamamlamak için e-posta adresini doğrulaman gerekiyor.

E-POSTA ADRESİMİ DOĞRULA:
{{ $url }}

Bu hesabı sen oluşturmadıysan bu e-postayı dikkate almayabilirsin. Hesabın doğrulanmaz ve başka bir işlem yapman gerekmez.

İyi günler,
{{ $brandName }} ekibi

© {{ date('Y') }} {{ $brandName }} · Tüm hakları saklıdır.
{{ url('/') }}
