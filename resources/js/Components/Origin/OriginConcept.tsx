import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { hasConcept } from '@/Components/Ui/Picture';
import { NightSkyArt } from '@/Components/Ui/Polaroid';
import { Badge } from '@/Components/Ui/Typography';
import { t } from '@/i18n/pt-BR';

/** Illustrations of /origem still being generated (prompts in docs/ovniporto-ilustracoes-origem.md). */
export const ORIGIN_CONCEPTS = [
    'origin-journey',
    'niva-abduction',
    'stone-star-night',
    'atlas-globe',
    'cachi-casa-cueva',
    'werner-stones',
] as const;

export type OriginConceptSlug = (typeof ORIGIN_CONCEPTS)[number];

/**
 * A concept scene of the origin pages: the illustration once it is in the concept manifest, until
 * then a night-sky frame that says "conceito em produção". Never shown as a photo. Positioning comes
 * from `className` (relative by default, or absolute inset-0 inside a sized frame).
 */
export function OriginConcept({
    slug,
    sizes,
    className = 'relative',
}: {
    slug: OriginConceptSlug;
    sizes?: string;
    className?: string;
}) {
    if (hasConcept(slug)) {
        return <ConceptImage slug={slug} sizes={sizes} className={className} />;
    }

    return (
        <div role="img" aria-label={t.origin.conceptPending} className={`overflow-hidden ${className}`}>
            <NightSkyArt seed={slug.length} />
            <p className="absolute inset-x-0 bottom-0 p-5 font-script text-2xl text-moonlight/85">
                {t.origin.conceptPending}
            </p>
            <Badge tone="horizon" className="absolute top-3 right-3">
                {t.concept.badge}
            </Badge>
        </div>
    );
}
