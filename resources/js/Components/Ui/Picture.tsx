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

/** Same widths as PublicImageLibrary::WIDTHS (server) and ProcessSightingPhoto::WIDTHS. */
export const RESPONSIVE_WIDTHS = [400, 800, 1200, 1600] as const;

/** AVIF/WebP srcsets and the 20 px placeholder of a content image, as the server writes them. */
export interface PhotoSources {
    webp: string;
    avif: string | null;
    placeholder: string | null;
    width?: number;
    height?: number;
}

const UPLOADED = /^(.*\/[0-9a-f-]{36})\.webp$/;

/**
 * Panel uploads ("…/{uuid}.webp" on the public disk) always have their AVIF/WebP widths and
 * placeholder next to them (PublicImageLibrary, `images:responsive`). Anything else (seeded
 * demo files, external URLs) gets no sources and loads as a plain lazy image.
 */
export function uploadedSources(url: string | null | undefined): PhotoSources | null {
    const base = url?.match(UPLOADED)?.[1];
    if (!base || base.includes('/demo/')) return null;
    const srcset = (format: 'avif' | 'webp') => RESPONSIVE_WIDTHS.map((w) => `${base}-${w}.${format} ${w}w`).join(', ');

    return { webp: srcset('webp'), avif: srcset('avif'), placeholder: `${base}-20.webp` };
}

/**
 * Content image (products, partners, diary, terrain, reports): AVIF, then WebP, then the
 * original, over its blurred placeholder; lazy unless `priority`. Without sources it is a
 * plain lazy <img>, so legacy files keep working.
 */
export function ContentImage({
    src,
    alt,
    sources = uploadedSources(src),
    sizes = '100vw',
    priority = false,
    className = '',
    imgClassName = 'h-full w-full object-cover',
}: {
    src: string;
    alt: string;
    sources?: PhotoSources | null;
    sizes?: string;
    priority?: boolean;
    className?: string;
    imgClassName?: string;
}) {
    const img = (
        <img
            src={src}
            alt={alt}
            width={sources?.width}
            height={sources?.height}
            loading={priority ? 'eager' : 'lazy'}
            fetchPriority={priority ? 'high' : 'auto'}
            decoding="async"
            className={imgClassName}
        />
    );
    if (!sources) {
        return className ? <span className={`block ${className}`}>{img}</span> : img;
    }

    return (
        <picture
            className={`block bg-cover bg-center ${className}`}
            style={sources.placeholder ? { backgroundImage: `url("${sources.placeholder}")` } : undefined}
        >
            {sources.avif && <source type="image/avif" srcSet={sources.avif} sizes={sizes} />}
            <source type="image/webp" srcSet={sources.webp} sizes={sizes} />
            {img}
        </picture>
    );
}
