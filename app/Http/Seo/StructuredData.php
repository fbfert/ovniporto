<?php

namespace App\Http\Seo;

/** schema.org JSON-LD built from data the page already shows publicly. */
final class StructuredData
{
    private const CONTEXT = 'https://schema.org';

    /** @return array<string, mixed> */
    public static function organization(): array
    {
        return [
            '@context' => self::CONTEXT,
            '@type' => 'Organization',
            'name' => (string) Seo::text('site_name'),
            'url' => Seo::appUrl(),
            'logo' => Seo::appUrl().'/brand/seal-512.webp',
            'description' => (string) Seo::text('default_description'),
            'address' => self::address(),
        ];
    }

    /**
     * @param  array{slug: string, title: string, excerpt: ?string, publishedAt: string}  $post
     * @return array<string, mixed>
     */
    public static function article(array $post, string $image): array
    {
        return array_filter([
            '@context' => self::CONTEXT,
            '@type' => 'Article',
            'headline' => $post['title'],
            'description' => $post['excerpt'],
            'image' => str_starts_with($image, 'http') ? $image : Seo::appUrl().$image,
            'datePublished' => $post['publishedAt'] ?: null,
            'url' => route('diary.post', ['slug' => $post['slug']]),
            'author' => ['@type' => 'Organization', 'name' => (string) Seo::text('site_name')],
            'publisher' => ['@type' => 'Organization', 'name' => (string) Seo::text('site_name')],
        ], fn ($value) => $value !== null);
    }

    /**
     * @param  list<array{question: string, answerHtml: string}>  $faqs
     * @return array<string, mixed>
     */
    public static function faqPage(array $faqs): array
    {
        return [
            '@context' => self::CONTEXT,
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim(strip_tags($faq['answerHtml']))],
            ], $faqs),
        ];
    }

    /**
     * The future runway: a place in planning, never described as open.
     *
     * @return array<string, mixed>
     */
    public static function place(): array
    {
        return [
            '@context' => self::CONTEXT,
            '@type' => 'Place',
            'name' => (string) Seo::text('site_name'),
            'description' => (string) Seo::text('pages.place.description'),
            'url' => route('place'),
            'address' => self::address(),
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) config('ovniporto.location.lat'),
                'longitude' => (float) config('ovniporto.location.lng'),
            ],
        ];
    }

    /** @return array<string, string> */
    private static function address(): array
    {
        return [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Lages',
            'addressRegion' => 'SC',
            'addressCountry' => 'BR',
        ];
    }

    /**
     * The Cachi dossier as a reference: the article, what it is about (the place and Werner Jaisli),
     * the people it mentions, every source it cites, the breadcrumb and the visible summary as FAQ.
     * The place has no coordinates while the dossier only has approximate ones.
     *
     * @param  array<string, mixed>  $dossier
     * @return array<string, mixed>
     */
    public static function cachiReference(array $dossier, string $description, string $image): array
    {
        $reference = $dossier['reference'];
        $url = Seo::appUrl().'/origem/cachi';
        $chapters = array_column($dossier['chapters'], null, 'id');

        return [
            '@context' => self::CONTEXT,
            '@graph' => [
                [
                    '@type' => 'Article',
                    '@id' => "{$url}#article",
                    'url' => $url,
                    'mainEntityOfPage' => $url,
                    'headline' => $reference['headline'],
                    'alternativeHeadline' => $dossier['title'],
                    'description' => $description,
                    'image' => Seo::appUrl().$image,
                    'inLanguage' => 'pt-BR',
                    'datePublished' => $reference['publishedAt'],
                    'dateModified' => $reference['updatedAt'],
                    'author' => ['@id' => self::organizationId()],
                    'publisher' => ['@id' => self::organizationId()],
                    'isPartOf' => ['@id' => Seo::appUrl().'/#website'],
                    'about' => [['@id' => "{$url}#place"], ['@id' => "{$url}#werner"]],
                    'mentions' => self::cachiMentions($chapters['personagens']['people'] ?? [], $reference['person']['name']),
                    'keywords' => implode(', ', $reference['keywords']),
                    'citation' => array_map(self::citation(...), $chapters['fontes']['sources'] ?? []),
                ],
                self::cachiPlace($reference, $url),
                self::cachiPerson($reference['person'], $url),
                ['@id' => self::organizationId(), ...array_diff_key(self::organization(), ['@context' => true])],
                ['@type' => 'WebSite', '@id' => Seo::appUrl().'/#website', 'name' => (string) Seo::text('site_name'), 'url' => Seo::appUrl(), 'inLanguage' => 'pt-BR'],
                self::breadcrumb([
                    [(string) Seo::text('breadcrumb_home'), Seo::appUrl()],
                    [(string) Seo::text('pages.origin.title'), Seo::appUrl().'/origem'],
                    [$reference['name'], $url],
                ]),
                ['@id' => "{$url}#faq", ...array_diff_key(self::faqPage(array_map(
                    fn (array $item) => ['question' => $item['question'], 'answerHtml' => $item['answer']],
                    $dossier['summary']['items'],
                )), ['@context' => true])],
            ],
        ];
    }

    private static function organizationId(): string
    {
        return Seo::appUrl().'/#organization';
    }

    /**
     * @param  list<array{name: string, role: string}>  $people
     * @return list<array<string, string>>
     */
    private static function cachiMentions(array $people, string $subject): array
    {
        return array_values(array_map(
            fn (array $person) => ['@type' => 'Person', 'name' => $person['name'], 'description' => $person['role']],
            array_filter($people, fn (array $person) => $person['name'] !== $subject),
        ));
    }

    /**
     * @param  array{title: string, publisher: string, date: string, url: string}  $source
     * @return array<string, mixed>
     */
    private static function citation(array $source): array
    {
        return array_filter([
            '@type' => 'CreativeWork',
            'name' => $source['title'],
            'url' => $source['url'],
            'datePublished' => $source['date'] ?: null,
            'publisher' => ['@type' => 'Organization', 'name' => $source['publisher']],
        ]);
    }

    /**
     * @param  array<string, mixed>  $reference
     * @return array<string, mixed>
     */
    private static function cachiPlace(array $reference, string $url): array
    {
        $place = $reference['place'];

        return [
            '@type' => ['Place', 'TouristAttraction'],
            '@id' => "{$url}#place",
            'name' => $reference['name'],
            'alternateName' => $place['alternateNames'],
            'description' => $place['description'],
            'url' => $url,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $place['area'],
                'addressLocality' => $place['locality'],
                'addressRegion' => $place['region'],
                'addressCountry' => $place['country'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $person
     * @return array<string, mixed>
     */
    private static function cachiPerson(array $person, string $url): array
    {
        return [
            '@type' => 'Person',
            '@id' => "{$url}#werner",
            'name' => $person['name'],
            'alternateName' => $person['alternateNames'],
            'nationality' => ['@type' => 'Country', 'name' => $person['nationality']],
            'deathDate' => $person['deathYear'],
            'deathPlace' => ['@type' => 'Country', 'name' => $person['deathPlace']],
            'description' => $person['description'],
        ];
    }

    /**
     * @param  list<array{0: string, 1: string}>  $crumbs  name and absolute URL, from the root down
     * @return array<string, mixed>
     */
    private static function breadcrumb(array $crumbs): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                fn (array $crumb, int $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $crumb[0], 'item' => $crumb[1]],
                $crumbs,
                array_keys($crumbs),
            ),
        ];
    }

    /**
     * An ovnipuerto of the Atlas, with confirmed coordinates only.
     *
     * @return array<string, mixed>
     */
    public static function atlasPlace(string $name, string $slug, float $lat, float $lng): array
    {
        return [
            '@context' => self::CONTEXT,
            '@type' => 'Place',
            'name' => $name,
            'url' => route('origin.atlas.case', $slug),
            'geo' => ['@type' => 'GeoCoordinates', 'latitude' => $lat, 'longitude' => $lng],
        ];
    }
}
