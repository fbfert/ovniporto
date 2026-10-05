import { t } from '@/i18n/pt-BR';
import { Picture, type ConceptSlug } from './Picture';
import { Badge } from './Typography';

/**
 * An illustration is never shown without saying so: the badge always rides along. "conceito" (default)
 * when it shows how the OVNIPORTO will look; "ilustração" for a relato or another place.
 */
export function ConceptImage({
    slug,
    sizes,
    className = '',
    pictureClassName = '',
    imgClassName,
    badgeClassName = 'top-3 right-3',
    badge = t.concept.badge,
    priority = false,
}: {
    slug: ConceptSlug;
    sizes?: string;
    className?: string;
    pictureClassName?: string;
    imgClassName?: string;
    badgeClassName?: string;
    badge?: string;
    priority?: boolean;
}) {
    return (
        <div className={`relative overflow-hidden ${className}`}>
            <Picture
                slug={slug}
                alt={t.concept.alt[slug]}
                sizes={sizes}
                priority={priority}
                className={`h-full w-full ${pictureClassName}`}
                imgClassName={imgClassName}
            />
            <Badge tone="horizon" className={`absolute ${badgeClassName}`}>
                {badge}
            </Badge>
        </div>
    );
}
