Merhaba{{ filled($user->name ?? null) ? ' '.$user->name : '' }},

{{ $brandName }} hesabını kullanmaya başlamak için e-posta adresini doğrulaman gerekiyor.

E-posta adresini doğrula:
{{ $url }}

Bu hesabı sen oluşturmadıysan bu e-postayı yok sayabilirsin. Herhangi bir işlem yapmana gerek yok.

İyi günler,
{{ $brandName }} ekibi

© {{ date('Y') }} {{ $brandName }}. Tüm hakları saklıdır.
