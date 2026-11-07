<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ $brandName }} - E-posta doğrulama</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#20242b;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f4f6f8;padding:36px 14px;">
<tr><td align="center">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:600px;">
<tr><td align="center" style="padding:0 0 24px;">
@if ($logoUrl)
<a href="{{ url('/') }}" style="display:inline-block;text-decoration:none;"><img src="{{ $logoUrl }}" alt="{{ $brandName }}" style="display:block;max-width:180px;max-height:58px;width:auto;height:auto;border:0;"></a>
@else
<a href="{{ url('/') }}" style="font-size:25px;line-height:1.2;font-weight:700;letter-spacing:-.6px;color:#17191d;text-decoration:none;">{{ $brandName }}</a>
@endif
</td></tr>
<tr><td style="background:#ffffff;border:1px solid #e8ebef;border-radius:12px;padding:40px 42px;">
<p style="margin:0 0 10px;font-size:23px;line-height:1.35;font-weight:700;color:#17191d;">Merhaba{{ filled($user->name ?? null) ? ' '.e($user->name) : '' }},</p>
<p style="margin:0 0 26px;font-size:15px;line-height:1.75;color:#596273;">{{ $brandName }} hesabını güvenle kullanmaya başlamak için e-posta adresini doğrulaman gerekiyor. Aşağıdaki düğmeye tıklayarak işlemi tamamlayabilirsin.</p>
<table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto 28px;"><tr><td align="center" bgcolor="#1769e0" style="border-radius:7px;"><a href="{{ $url }}" target="_blank" style="display:inline-block;padding:14px 25px;font-size:14px;line-height:1.2;font-weight:700;color:#ffffff;text-decoration:none;border-radius:7px;">E-posta adresimi doğrula</a></td></tr></table>
<p style="margin:0 0 8px;font-size:13px;line-height:1.7;color:#687385;">Düğme çalışmıyorsa aşağıdaki bağlantıyı tarayıcının adres çubuğuna kopyalayabilirsin:</p>
<p style="margin:0 0 26px;word-break:break-all;font-size:12px;line-height:1.7;"><a href="{{ $url }}" style="color:#1769e0;text-decoration:underline;">{{ $url }}</a></p>
<div style="height:1px;background:#e9edf2;margin:0 0 22px;"></div>
<p style="margin:0 0 18px;font-size:13px;line-height:1.7;color:#687385;">Bu hesabı sen oluşturmadıysan bu e-postayı güvenle yok sayabilirsin. Herhangi bir işlem yapmana gerek yok.</p>
<p style="margin:0;font-size:14px;line-height:1.7;color:#414957;">İyi günler,<br><strong style="color:#20242b;">{{ $brandName }} ekibi</strong></p>
</td></tr>
<tr><td align="center" style="padding:22px 12px 0;font-size:11px;line-height:1.7;color:#929baa;">Bu otomatik bir bilgilendirme e-postasıdır. Lütfen bu mesaja yanıt verme.<br>© {{ date('Y') }} {{ $brandName }}. Tüm hakları saklıdır.<br><a href="{{ url('/') }}" style="color:#7c8797;text-decoration:none;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a></td></tr>
</table></td></tr></table>
</body></html>
