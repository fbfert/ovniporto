{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($entries as $entry)
    <url>
        <loc>{{ $baseUrl }}{{ $entry->path === '/' ? '' : $entry->path }}</loc>
@if ($entry->lastModified)
        <lastmod>{{ $entry->lastModified->format('Y-m-d') }}</lastmod>
@endif
    </url>
@endforeach
</urlset>
