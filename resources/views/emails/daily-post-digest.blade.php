<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ografinin günlük gönderi özeti</title>
</head>
<body style="margin:0;background:#f3f4f6;color:#111827;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;">
    <tr>
        <td align="center" style="padding:24px 12px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 28px;border-bottom:1px solid #e5e7eb;">
                        <div style="font-size:22px;font-weight:700;color:#111827;">Ografi</div>
                        <div style="margin-top:6px;font-size:14px;line-height:1.5;color:#4b5563;">Bugünün öne çıkan gönderileri</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 28px 8px;">
                        <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#374151;">Merhaba {{ $recipient->name }}, bugün yayımlanan gönderilerden sizin için en fazla 10 tanesini seçtik.</p>

                        @foreach ($posts as $post)
                            @php
                                $summary = Illuminate\Support\Str::limit(
                                    trim(strip_tags((string) ($post->excerpt ?: $post->content))),
                                    180
                                );
                            @endphp
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding:0 0 20px;">
                                        <div style="font-size:12px;line-height:1.4;color:#2563eb;font-weight:700;">
                                            {{ $post->category?->name ?? 'Gönderi' }}
                                        </div>
                                        <h2 style="margin:5px 0 8px;font-size:18px;line-height:1.35;font-weight:700;">
                                            <a href="{{ route('blog.post', ['post' => $post->slug]) }}" style="color:#111827;text-decoration:none;">{{ $post->title }}</a>
                                        </h2>
                                        @if ($summary !== '')
                                            <p style="margin:0 0 10px;font-size:14px;line-height:1.6;color:#4b5563;">{{ $summary }}</p>
                                        @endif
                                        <a href="{{ route('blog.post', ['post' => $post->slug]) }}" style="font-size:14px;font-weight:700;color:#1d4ed8;text-decoration:none;">Gönderiyi oku</a>
                                    </td>
                                </tr>
                                @unless ($loop->last)
                                    <tr><td style="height:1px;background:#e5e7eb;"></td></tr>
                                    <tr><td style="height:20px;"></td></tr>
                                @endunless
                            </table>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:12px;line-height:1.6;color:#6b7280;">
                        Bu e-postayı günlük özet tercihiniz açık olduğu için aldınız.
                        <a href="{{ $preferencesUrl }}" style="color:#374151;">E-posta ayarları</a>
                        &nbsp;·&nbsp;
                        <a href="{{ $unsubscribeUrl }}" style="color:#374151;">Abonelikten çık</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
