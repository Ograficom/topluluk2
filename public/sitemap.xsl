<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <html lang="tr">
            <head>
                <meta charset="UTF-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1"/>
                <title>OgraFi - XML Sitemap</title>
                <style>
                    *{box-sizing:border-box}
                    body{margin:0;background:#f8fafc;color:#172033;font:14px -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
                    .wrap{max-width:1000px;margin:48px auto;padding:0 20px}
                    .top{padding:28px;background:#fff;border:1px solid #e5e7eb;border-radius:18px;margin-bottom:18px}
                    .eyebrow{color:#2563eb;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
                    h1{font-size:26px;letter-spacing:-.7px;margin:9px 0}
                    .desc{color:#64748b;line-height:1.6;margin:0}
                    .count{display:inline-flex;margin-top:18px;padding:8px 12px;border-radius:9px;background:#eff6ff;color:#1d4ed8;font-weight:700}
                    .list{overflow:hidden;background:#fff;border:1px solid #e5e7eb;border-radius:16px}
                    .row{display:grid;grid-template-columns:minmax(0,1fr) 190px;gap:18px;padding:16px 20px;border-bottom:1px solid #eef0f3;align-items:center}
                    .row:last-child{border-bottom:0}
                    .url{color:#2563eb;text-decoration:none;overflow-wrap:anywhere;line-height:1.55}
                    .url:hover{text-decoration:underline}
                    .date{color:#64748b;font-size:12px;text-align:right;white-space:nowrap}
                    .head{background:#f8fafc;color:#64748b;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
                    .foot{color:#94a3b8;text-align:center;font-size:12px;padding:20px}
                    @media(max-width:640px){.wrap{margin:20px auto;padding:0 12px}.top{padding:20px}h1{font-size:22px}.row{grid-template-columns:minmax(0,1fr);gap:6px;padding:14px}.date{text-align:left;white-space:normal}.head{display:none}}
                </style>
            </head>
            <body>
                <main class="wrap">
                    <header class="top">
                        <div class="eyebrow">OgraFi · Arama motoru haritası</div>
                        <h1>XML Sitemap</h1>
                        <p class="desc">Bu dosya, arama motorlarının OgraFi üzerindeki herkese açık sayfaları keşfetmesine yardımcı olur.</p>
                        <div class="count"><xsl:value-of select="count(s:urlset/s:url)"/> URL listeleniyor</div>
                    </header>
                    <section class="list">
                        <div class="row head"><div>Sayfa adresi</div><div class="date">Son güncelleme</div></div>
                        <xsl:for-each select="s:urlset/s:url">
                            <div class="row">
                                <div><a class="url" target="_blank"><xsl:attribute name="href"><xsl:value-of select="s:loc"/></xsl:attribute><xsl:value-of select="s:loc"/></a></div>
                                <div class="date"><xsl:value-of select="s:lastmod"/></div>
                            </div>
                        </xsl:for-each>
                    </section>
                    <div class="foot">OgraFi XML Sitemap · XML standardında sunulmaktadır.</div>
                </main>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
