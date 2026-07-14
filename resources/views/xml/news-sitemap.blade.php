@php echo '<' . '?xml version="1.0" encoding="UTF-8"?' . '>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
@foreach($items as $item)
    <url>
        <loc>{{ $item['loc'] }}</loc>
        <news:news>
            <news:publication>
                <news:name>{{ $item['publication_name'] }}</news:name>
                <news:language>{{ $item['language'] }}</news:language>
            </news:publication>
            <news:publication_date>{{ $item['publication_date'] }}</news:publication_date>
            <news:title>{{ $item['title'] }}</news:title>
        </news:news>
    </url>
@endforeach
</urlset>
