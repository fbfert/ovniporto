<?php

namespace App\Http\Seo;

use App\Application\Content\UseCases\GetOgImage;
use App\Domain\Content\Sharing\OgKind;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Sharing metadata of pages whose title and image come from content.
 * Reads only what the page already received from its use case: nothing private.
 */
final readonly class ContentSeo
{
    private const DESCRIPTION_LIMIT = 155;

    public function __construct(private GetOgImage $ogImages) {}

    public function home(): Seo
    {
        return Seo::forRoute('home')->withJsonLd(StructuredData::organization());
    }

    /**
     * @param  array{name: string, shortDescription?: ?string, description?: ?string, slug: string}  $product
     * @param  array<string, mixed>  $structuredData
     */
    public function product(array $product, array $structuredData): Seo
    {
        return new Seo(
            title: $product['name'],
            description: $this->excerpt($product['shortDescription'] ?? $product['description'] ?? null),
            image: $this->ogImage(OgKind::Product, $product['slug']),
            type: 'product',
            jsonLd: [$structuredData],
        );
    }

    /** @param array{name: string, slug: string, shortDescription?: ?string} $partner */
    public function partner(array $partner): Seo
    {
        return new Seo(
            title: $partner['name'],
            description: $this->excerpt($partner['shortDescription'] ?? null, Seo::forRoute('region')->description),
            image: $this->ogImage(OgKind::Partner, $partner['slug']),
        );
    }

    /** @param array{slug: string, title: string, excerpt: ?string, cover: ?string, publishedAt: string} $post */
    public function post(array $post): Seo
    {
        $image = $this->ogImage(OgKind::Post, $post['slug']);

        return new Seo(
            title: $post['title'],
            description: $this->excerpt($post['excerpt'], Seo::forRoute('diary')->description),
            image: $image,
            type: 'article',
            jsonLd: [StructuredData::article($post, $image)],
        );
    }

    /** @param array{sighting: array{id: int, type: string, description: string, observedDate: string}, ownPending: bool} $page */
    public function sighting(array $page): Seo
    {
        if ($page['ownPending']) {
            return new Seo(title: (string) Seo::text('pending_sighting_title'), description: Seo::forRoute(null)->description, indexable: false);
        }

        $sighting = $page['sighting'];
        $date = Carbon::parse($sighting['observedDate'])->locale('pt_BR')->translatedFormat('j \d\e F \d\e Y');

        return new Seo(
            title: Seo::text("sighting_types.{$sighting['type']}").' · '.$date,
            description: $this->excerpt($sighting['description']),
            image: $this->ogImage(OgKind::Sighting, (string) $sighting['id']),
            type: 'article',
        );
    }

    /** @param list<array{question: string, answerHtml: string}> $faqs */
    public function faq(array $faqs): Seo
    {
        return Seo::forRoute('faq')->withJsonLd(StructuredData::faqPage($faqs));
    }

    public function place(): Seo
    {
        return Seo::forRoute('place')->withJsonLd(StructuredData::place());
    }

    /** @param array<string, mixed> $dossier */
    public function cachi(array $dossier): Seo
    {
        $seo = Seo::forRoute('origin.cachi');

        return new Seo($seo->title, $seo->description, $seo->image, 'article', jsonLd: [StructuredData::cachiReference($dossier, $seo->description, $seo->image)]);
    }

    /** The relato is a story told by Julean, never presented as a document. */
    public function relato(): Seo
    {
        $seo = Seo::forRoute('origin.relato');

        return new Seo($seo->title, $seo->description, $seo->image, 'article', jsonLd: [StructuredData::relato((string) $seo->title, $seo->description, $seo->image)]);
    }

    /**
     * Title, description and the case's own photo; a Place with coordinates only when they are not provisional.
     *
     * @param  array<string, mixed>  $case
     */
    public function atlasCase(array $case): Seo
    {
        $image = isset($case['image']['file']) ? "/origin/{$case['image']['file']}.jpg" : Seo::DEFAULT_IMAGE;
        $seo = new Seo(
            title: (string) Seo::text('atlas_case_title', ['name' => (string) $case['name']]),
            description: Str::limit(trim(implode(' · ', array_filter([(string) $case['country'], (string) ($case['category'] ?? '')]))), self::DESCRIPTION_LIMIT),
            image: $image,
            type: 'article',
        );
        $coordinates = $case['coordinates'] ?? null;

        return is_array($coordinates) && ! ($coordinates['approximate'] ?? true)
            ? $seo->withJsonLd(StructuredData::atlasPlace((string) $case['name'], (string) $case['slug'], (float) $coordinates['lat'], (float) $coordinates['lng']))
            : $seo;
    }

    public function order(string $number): Seo
    {
        return new Seo(title: (string) Seo::text('order_title', ['number' => $number]), description: Seo::forRoute(null)->description, indexable: false);
    }

    private function ogImage(OgKind $kind, string $key): string
    {
        $card = $this->ogImages->card($kind, $key);

        return $card === null
            ? Seo::DEFAULT_IMAGE
            : route('og.image', ['kind' => $kind->value, 'key' => $key, 'version' => $card->version()], absolute: false);
    }

    private function excerpt(?string $text, ?string $fallback = null): string
    {
        $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

        return $plain === '' ? ($fallback ?? Seo::forRoute(null)->description) : Str::limit($plain, self::DESCRIPTION_LIMIT);
    }
}
