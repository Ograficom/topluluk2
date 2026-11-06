<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9">
    <xsl:output method="text" encoding="UTF-8"/>
    <xsl:template match="/">
        <xsl:text>&lt;?xml version="1.0" encoding="UTF-8"?&gt;&#10;</xsl:text>
        <xsl:text>&lt;urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"&gt;&#10;</xsl:text>
        <xsl:for-each select="s:urlset/s:url">
            <xsl:text>  &lt;url&gt;&#10;    &lt;loc&gt;</xsl:text>
            <xsl:value-of select="s:loc"/>
            <xsl:text>&lt;/loc&gt;&#10;</xsl:text>
            <xsl:if test="s:lastmod">
                <xsl:text>    &lt;lastmod&gt;</xsl:text>
                <xsl:value-of select="s:lastmod"/>
                <xsl:text>&lt;/lastmod&gt;&#10;</xsl:text>
            </xsl:if>
            <xsl:text>  &lt;/url&gt;&#10;</xsl:text>
        </xsl:for-each>
        <xsl:text>&lt;/urlset&gt;</xsl:text>
    </xsl:template>
</xsl:stylesheet>
