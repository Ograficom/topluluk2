<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $brandName }} | E-posta doğrulama</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#172033;-webkit-text-size-adjust:100%;">
    <div style="display:none;font-size:1px;color:#f5f7fb;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">{{ $brandName }} hesabını etkinleştirmek için e-posta adresini doğrula.</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f7fb;">
        <tr>
            <td align="center" style="padding:42px 16px 46px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;">
                    <tr>
                        <td align="center" style="padding:0 0 27px;">
                            <a href="{{ url('/') }}" style="display:inline-block;text-decoration:none;">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" width="156" style="display:block;width:auto;max-width:156px;max-height:48px;height:auto;border:0;outline:none;text-decoration:none;">
                                @else
                                    <span style="font-size:27px;line-height:34px;font-weight:700;letter-spacing:-1px;color:#172033;">{{ $brandName }}</span>
                                @endif
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#ffffff;border:1px solid #e7ebf2;border-radius:14px;overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="5" style="height:5px;background-color:#0d6efd;font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:38px 42px 40px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
                                            <tr>
                                                <td style="background:#edf5fe;border:1px solid #dceaff;border-radius:20px;padding:7px 12px;font-size:11px;line-height:14px;font-weight:700;letter-spacing:.7px;color:#0b5ed7;">HESAP GÜVENLİĞİ</td>
                                            </tr>
                                        </table>
                                        <h1 style="margin:0 0 14px;font-size:25px;line-height:1.3;font-weight:700;letter-spacing:-.55px;color:#172033;">E-posta adresini doğrula</h1>
                                        <p style="margin:0 0 10px;font-size:15px;line-height:1.6;color:#263247;">Merhaba{{ filled($user->name ?? null) ? ' '.e($user->name) : '' }},</p>
                                        <p style="margin:0 0 27px;font-size:14px;line-height:1.8;color:#657187;">{{ $brandName }} topluluğuna katıldığın için teşekkürler. Hesabını güvence altına almak ve üyeliğini tamamlamak için e-posta adresini doğrulaman gerekiyor.</p>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 27px;">
                                            <tr>
                                                <td align="center" bgcolor="#0d6efd" style="border-radius:8px;">
                                                    <a href="{{ $url }}" target="_blank" style="display:block;padding:15px 22px;font-size:14px;line-height:20px;font-weight:700;letter-spacing:.1px;color:#ffffff;text-decoration:none;border-radius:8px;">E-posta adresimi doğrula&nbsp; &rarr;</a>
                                                </td>
                                            </tr>
                                        </table>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f7f9fc;border:1px solid #e9edf4;border-radius:9px;margin:0 0 25px;">
                                            <tr>
                                                <td style="padding:15px 16px;">
                                                    <p style="margin:0 0 7px;font-size:12px;line-height:18px;font-weight:700;color:#344054;">Buton çalışmıyor mu?</p>
                                                    <p style="margin:0 0 8px;font-size:12px;line-height:1.6;color:#7a8496;">Aşağıdaki bağlantıyı tarayıcına kopyalayabilirsin:</p>
                                                    <p style="margin:0;font-size:12px;line-height:1.7;word-break:break-all;"><a href="{{ $url }}" style="color:#0b5ed7;text-decoration:underline;">{{ $url }}</a></p>
                                                </td>
                                            </tr>
                                        </table>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr><td style="border-top:1px solid #edf0f5;padding-top:20px;">
                                                <p style="margin:0;font-size:12px;line-height:1.8;color:#7a8496;">Bu hesabı sen oluşturmadıysan bu e-postayı dikkate almayabilirsin. Hesabın doğrulanmaz ve başka bir işlem yapman gerekmez.</p>
                                            </td></tr>
                                        </table>
                                        <p style="margin:25px 0 0;font-size:13px;line-height:1.7;color:#526075;">İyi günler,<br><strong style="color:#172033;">{{ $brandName }} ekibi</strong></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:23px 18px 0;">
                            <p style="margin:0 0 7px;font-size:11px;line-height:1.7;color:#8993a5;">Bu e-posta, {{ $brandName }} üyelik işlemin kapsamında otomatik olarak gönderilmiştir.</p>
                            <p style="margin:0;font-size:11px;line-height:1.8;color:#a0a8b6;">© {{ date('Y') }} {{ $brandName }} · Tüm hakları saklıdır.</p>
                            <p style="margin:7px 0 0;font-size:11px;line-height:1.7;"><a href="{{ url('/') }}" style="color:#0b5ed7;text-decoration:none;font-weight:600;">{{ parse_url(url('/'), PHP_URL_HOST) }}</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
