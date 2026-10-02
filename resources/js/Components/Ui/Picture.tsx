import manifest from '@/data/concept.json';

export type ConceptSlug = keyof typeof manifest;

interface ConceptEntry {
    width: number;
    height: number;
    widths: number[];
    lqip: string;
}

const entries: Record<string, ConceptEntry | undefined> = manifest;

export function hasConcept(slug: string | null | undefined): slug is ConceptSlug {
    return !!slug && slug in entries;
}

function srcSet(slug: string, widths: number[], format: 'avif' | 'webp') {
    return widths.map((w) => `/concept/${slug}-${w}.${format} ${w}w`).join(', ');
}

/**
 * Responsive concept illustration: AVIF, then WebP, then the JPEG fallback,
 * over a blurred 20px LQIP so the frame never flashes empty. Lazy unless
 * `priority` (only the hero).
 */
export function Picture({
    slug,
    alt,
    sizes = '100vw',
    priority = false,
    className = '',
    imgClassName = 'h-full w-full object-cover',
}: {
    slug: ConceptSlug;
    alt: string;
    sizes?: string;
    priority?: boolean;
    className?: string;
    imgClassName?: string;
}) {
    const entry = entries[slug];
    if (!entry) return null;

    return (
        <picture
            className={`block bg-cover bg-center ${className}`}
            style={{ backgroundImage: `url("${entry.lqip}")` }}
        >
            <source type="image/avif" srcSet={srcSet(slug, entry.widths, 'avif')} sizes={sizes} />
            <source type="image/webp" srcSet={srcSet(slug, entry.widths, 'webp')} sizes={sizes} />
            <img
                src={`/concept/${slug}.jpg`}
                alt={alt}
                width={entry.width}
                height={entry.height}
                loading={priority ? 'eager' : 'lazy'}
                fetchPriority={priority ? 'high' : 'auto'}
                decoding="async"
                className={imgClassName}
            />
        </picture>
    );
}
