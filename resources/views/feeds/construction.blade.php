{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Diário da obra · OVNIPORTO Lages</title>
        <link>{{ url('/obra') }}</link>
        <description>A pista de pouso do planalto, pedra por pedra.</description>
        <language>pt-BR</language>
        <atom:link href="{{ url('/obra.rss') }}" rel="self" type="application/rss+xml" />
@foreach ($posts as $post)
        <item>
            <title>{{ $post['title'] }}</title>
            <link>{{ url('/obra/'.$post['slug']) }}</link>
            <guid isPermaLink="true">{{ url('/obra/'.$post['slug']) }}</guid>
            <pubDate>{{ \Illuminate\Support\Carbon::parse($post['publishedAt'])->toRfc2822String() }}</pubDate>
@if ($post['excerpt'])
            <description>{{ $post['excerpt'] }}</description>
@endif
        </item>
@endforeach
    </channel>
</rss>
