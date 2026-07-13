<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class RssController extends Controller
{
    public function index(): Response
    {
        $posts = Post::query()
            ->published()
            ->with([
                'category:id,name,slug',
                'author:id,name,username',
                'tags:id,name,slug',
            ])
            ->orderByDesc('published_at')
            ->limit(50)
            ->get();

        $siteName = (string) config('app.name', 'Blog');
        $siteUrl = url('/');
        $selfUrl = request()->url();

        $lastBuild = $posts->first()?->published_at ?? now();
        $lastBuildRfc = $lastBuild->copy()->setTimezone('UTC')->toRfc2822String();

        $xmlEscape = static fn (?string $value): string => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $cdata = static function (?string $value): string {
            $value = (string) $value;
            return str_replace(']]>', ']]]]><![CDATA[>', $value);
        };

        $itemsXml = '';
        foreach ($posts as $post) {
            $postUrl = route('blog.post', $post);
            $postDate = ($post->published_at ?? $post->created_at ?? now())
                ->copy()
                ->setTimezone('UTC')
                ->toRfc2822String();

            $descriptionText = trim((string) ($post->excerpt ?? ''));
            if ($descriptionText === '') {
                $descriptionText = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->content)) ?? ''), 200);
            }

            $contentHtml = (string) ($post->content ?? '');
            $authorName = (string) ($post->author?->name ?? '');

            $categories = [];
            if ($post->category?->name) {
                $categories[] = $post->category->name;
            }
            foreach ($post->tags as $tag) {
                if ($tag?->name) {
                    $categories[] = $tag->name;
                }
            }
            if ($authorName !== '') {
                $categories[] = 'Yazar: ' . $authorName;
            }
            $categories = array_values(array_unique(array_filter($categories)));

            $categoryXml = '';
            foreach ($categories as $cat) {
                $categoryXml .= '<category><![CDATA[' . $cdata($cat) . ']]></category>' . "\n";
            }

            $guid = $post->id ? "post:{$post->id}" : (string) $postUrl;

            $itemsXml .= "<item>\n";
            $itemsXml .= '<title><![CDATA[' . $cdata($post->title) . "]]></title>\n";
            $itemsXml .= '<link>' . $xmlEscape($postUrl) . "</link>\n";
            $itemsXml .= '<guid isPermaLink="false">' . $xmlEscape($guid) . "</guid>\n";
            $itemsXml .= '<pubDate>' . $xmlEscape($postDate) . "</pubDate>\n";
            if ($authorName !== '') {
                $itemsXml .= '<dc:creator><![CDATA[' . $cdata($authorName) . "]]></dc:creator>\n";
            }
            $itemsXml .= $categoryXml;
            $itemsXml .= '<description><![CDATA[' . $cdata($descriptionText) . "]]></description>\n";
            if ($contentHtml !== '') {
                $itemsXml .= '<content:encoded><![CDATA[' . $cdata($contentHtml) . "]]></content:encoded>\n";
            }
            $itemsXml .= "</item>\n";
        }

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
    <title>{$xmlEscape($siteName)}</title>
    <link>{$xmlEscape($siteUrl)}</link>
    <atom:link href="{$xmlEscape($selfUrl)}" rel="self" type="application/rss+xml" />
    <description>{$xmlEscape($siteName)} RSS</description>
    <language>tr</language>
    <lastBuildDate>{$xmlEscape($lastBuildRfc)}</lastBuildDate>
    <ttl>10</ttl>
{$itemsXml}</channel>
</rss>
XML;

        return response($xml, 200)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=300');
    }
}
