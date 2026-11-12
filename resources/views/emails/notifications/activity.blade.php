<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>OgraFi bildirimi</title>
</head>
<body style="margin:0;padding:0;background:#f3f6fb;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#172033;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6fb;padding:36px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;">
                    <tr>
                        <td align="center" style="padding:0 0 22px;">
                            <a href="{{ url('/') }}" style="display:inline-block;text-decoration:none;">
                                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" width="58" height="58" style="display:block;width:58px;height:58px;object-fit:contain;border:0;outline:none;text-decoration:none;margin:0 auto;">
                            </a>
                            <div style="padding-top:9px;font-size:21px;font-weight:800;letter-spacing:-.5px;color:#172033;">{{ $siteName }}</div>
                            <div style="padding-top:5px;font-size:12px;letter-spacing:1.6px;text-transform:uppercase;color:#8290a5;">Topluluğundan haber var</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#fff;border:1px solid #e5eaf2;border-radius:18px;overflow:hidden;">
                                <tr>
                                    <td style="height:5px;background:linear-gradient(90deg,#2563eb,#60a5fa);font-size:0;line-height:0;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="padding:34px 36px 12px;">
                                        <div style="font-size:12px;font-weight:700;letter-spacing:1.1px;text-transform:uppercase;color:#2563eb;">Yeni bildirim</div>
                                        <h1 style="margin:10px 0 8px;font-size:25px;line-height:1.25;letter-spacing:-.6px;color:#172033;">Merhaba {{ $recipientName }}!</h1>
                                        <p style="margin:0;font-size:15px;line-height:1.7;color:#64748b;">OgraFi hesabında yeni bir hareket var. Ayrıntıları aşağıda görebilirsin.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:18px 36px 8px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f7f9fd;border:1px solid #e9eef6;border-radius:14px;">
                                            <tr>
                                                <td style="padding:18px 20px 14px;">
                                                    <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#94a3b8;">Kimden</div>
                                                    <div style="padding-top:5px;font-size:16px;font-weight:700;color:#1e293b;">{{ $senderName }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 20px;"><div style="height:1px;background:#e6ebf3;"></div></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:14px 20px;">
                                                    <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#94a3b8;">Etkinlik</div>
                                                    <div style="padding-top:5px;font-size:15px;line-height:1.55;color:#334155;">{{ $summary }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 20px;"><div style="height:1px;background:#e6ebf3;"></div></td>
                                            </tr>
                                            <tr>
                                                <td style="padding:14px 20px 18px;">
                                                    <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#94a3b8;">Tarih ve saat</div>
                                                    <div style="padding-top:5px;font-size:14px;font-weight:600;color:#334155;">{{ $sentAt }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding:24px 36px 32px;">
                                        <a href="{{ $target }}" style="display:inline-block;padding:14px 27px;border-radius:10px;background:#2563eb;color:#fff;text-decoration:none;font-size:14px;font-weight:700;box-shadow:0 5px 12px rgba(37,99,235,.18);">OgraFi'de görüntüle <span style="padding-left:6px;">&#8594;</span></a>
                                        <p style="margin:18px 0 0;font-size:12px;line-height:1.6;color:#94a3b8;">Bu e-postada özel mesajının içeriği paylaşılmaz. Bildirim tercihlerin nedeniyle gönderilmiştir.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:22px 18px 0;font-size:12px;line-height:1.7;color:#94a3b8;">
                            <strong style="color:#64748b;">{{ $siteName }}</strong><br>
                            Bu otomatik bir bildirim e-postasıdır.<br>
                            <a href="{{ url('/') }}" style="color:#64748b;text-decoration:underline;">Siteyi ziyaret et</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
