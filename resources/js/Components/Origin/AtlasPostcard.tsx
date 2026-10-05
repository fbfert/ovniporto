import { ConceptImage } from '@/Components/Ui/ConceptImage';
import { hasConcept, type ConceptSlug } from '@/Components/Ui/Picture';
import { t } from '@/i18n/pt-BR';

/**
 * Cases whose postcard comes from another series: Cachi shares the night star of /origem, and Lages,
 * still a project, shows the concept of the future site (badge "conceito", never "ilustração").
 */
const SHARED: Record<string, { slug: string; badge: string }> = {
    cachi: { slug: 'stone-star-night', badge: t.concept.illustration },
    lages: { slug: 'overview', badge: t.concept.badge },
};

function postcardFor(caseSlug: string): { slug: ConceptSlug; badge: string } | null {
    const { slug, badge } = SHARED[caseSlug] ?? { slug: `atlas-ill-${caseSlug}`, badge: t.concept.illustration };

    return hasConcept(slug) ? { slug, badge } : null;
}

/** The illustrated postcard of an Atlas case (4:3, night), or nothing while it has none. */
export function AtlasPostcard({
    caseSlug,
    sizes,
    className = '',
    priority = false,
}: {
    caseSlug: string;
    sizes: string;
    className?: string;
    priority?: boolean;
}) {
    const postcard = postcardFor(caseSlug);
    if (!postcard) return null;

    return (
        <ConceptImage
            slug={postcard.slug}
            badge={postcard.badge}
            sizes={sizes}
            priority={priority}
            className={`aspect-[4/3] bg-night ${className}`}
            imgClassName="h-full w-full object-cover"
        />
    );
}
