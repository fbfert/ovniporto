import { t } from '@/i18n/pt-BR';
import { Picture, type ConceptSlug } from './Picture';
import { Badge } from './Typography';

/** A concept illustration is never shown without saying so: the "conceito" badge always rides along. */
export function ConceptImage({
    slug,
    sizes,
    className = '',
    pictureClassName = '',
    imgClassName,
    badgeClassName = 'top-3 right-3',
}: {
    slug: ConceptSlug;
    sizes?: string;
    className?: string;
    pictureClassName?: string;
    imgClassName?: string;
    badgeClassName?: string;
}) {
    return (
        <div className={`relative overflow-hidden ${className}`}>
            <Picture
                slug={slug}
                alt={t.concept.alt[slug]}
                sizes={sizes}
                className={`h-full w-full ${pictureClassName}`}
                imgClassName={imgClassName}
            />
            <Badge tone="horizon" className={`absolute ${badgeClassName}`}>
                {t.concept.badge}
            </Badge>
        </div>
    );
}
