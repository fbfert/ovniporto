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

    /** @return array<string, mixed> */
    public static function originArticle(string $title, string $description, string $image): array
    {
        return [
            '@context' => self::CONTEXT,
            '@type' => 'Article',
            'headline' => $title,
            'description' => $description,
            'image' => Seo::appUrl().$image,
            'inLanguage' => 'pt-BR',
            'publisher' => ['@type' => 'Organization', 'name' => (string) Seo::text('site_name')],
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
